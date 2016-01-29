<?php

namespace DIServer\Monitor;


use DIServer\Interfaces\IMonitor;
use DIServer\Services\Event;
use DIServer\Services\Log;

class SwooleTable implements IMonitor
{
	/**
	 * @var \swoole_table
	 */
	protected $table;
	protected $server;
	protected $workerNum = 0;
	protected $taskNum = 0;
	const FIELD = 'Num';
	const START_TIME = 0;
	const CONNECTION_NUM = 1;
	const ACCEPT_COUNT = 2;
	const CLOSE_COUNT = 3;
	const TASKING_NUM = 4;
	const REQUEST_NUM = 5;
	const WORKER_REQUEST_NUM = 6;
	const WORKER_SEND_COUNT_FIELD = 8;
	const WORKER_LAST_TASK_ID_FIELD = 9;
	const TASK_RECEIVE_COUNT_FIELD = 11;
	const TASK_FINISH_COUNT_FIELD = 12;
	const TASK_FAILED_COUNT_FIELD = 13;


	public function __construct(\swoole_server $server)
	{
		$defaultRow = 8;//至少有8个基本统计量
		$this->server = $server;
		$workerNum = $server->setting['worker_num'];
		$taskNum = $server->setting['task_worker_num'];
		$rowCount = $defaultRow + $workerNum * 2 + $taskNum * 4;
		$rowCount = log10($rowCount) / log10(2);//以2为底求需要的最小倍数
		$rowCount = (int)($rowCount + 1);//即便没有余数也+1取整保证留有一定的余地
		$rowCount = pow(2, $rowCount);//获得最接近的行数
		$this->table = new \swoole_table($rowCount);

		$this->table->column(self::FIELD, \swoole_table::TYPE_INT);
		$this->table->create();
		//初始化默认字段
		$this->Set(self::REQUEST_NUM);
		$this->Set(self::WORKER_REQUEST_NUM);
		$this->taskNum = $taskNum;
		$this->workerNum = $workerNum;
	}

	public function Bind()
	{
		Event::Add('OnTask', [$this, 'OnTask']);
		Event::Add('OnFinish', [$this, 'OnFinish']);
		Event::Add('AfterTaskSend', [$this, 'AfterTaskSend']);
	}

	public function All()
	{
		$base = [
			'MemoryUsage'     => (round(memory_get_usage(true) / 1024.0 / 1024.0, 2)) . " MB",
			'MemoryPeakUsage' => (round(memory_get_peak_usage(true) / 1024.0 / 1024.0, 2)) . " MB",
			'StartTime'       => date('Y-m-d H:i:s [e]', $this->server->stats()['start_time']),
			'ConnectingNum'   => $this->server->stats()['connection_num'],
			'AcceptCount'     => $this->server->stats()['accept_count'],
			'CloseCount'      => $this->server->stats()['close_count'],
			'TaskingNum'      => $this->server->stats()['tasking_num'],
		];
		for($i = 0; $i < $this->workerNum; $i++)
		{
			$base["Worker[$i]SendTaskCount"] = $this->GetWorkerSendCount($i);
		}
		for($i = 0; $i < $this->taskNum; $i++)
		{
			$base["TaskWorker[$i]ReceivedCount"] = $this->GetTaskerReceiveCount($this->workerNum + $i);
			$base["TaskWorker[$i]FinishedCount"] = $this->GetTaskerFinishCount($this->workerNum + $i);
			//$base["Worker[$i]LastTaskerID"]=>$this->GEt
		}

		return $base;
	}

	public function AfterTaskSend($task, $taskID)
	{
		//在Worker进程调用Task或者TaskWait的时候计数
		//Log::Debug("Monitor AfterTaskSend:$taskID");
		$this->Incr($this->workerSendCountField($this->server->worker_id));
	}

	public function OnTask(\swoole_server $server, $task_id, $from_id, $param)
	{
		//在Task进程触发OnTask的时候计数
		$this->Incr($this->taskReceiveCountField($server->worker_id));
		$pkTaskID = $from_id . '+' . $task_id;
		//Log::Debug("$from_id.$task_id is in {$server->worker_id}");
		$this->Set($pkTaskID, $server->worker_id);
		$this->Set($this->workerLastTaskIDField($from_id), $task_id);
	}

	public function OnFinish(\swoole_server $server, $task_id, $taskResult)
	{
		$pkTaskID = $server->worker_id . '+' . $task_id;
		$taskerID = $this->Get($pkTaskID);
		//在Worker进程触发OnFinish时计数
		$this->Incr($this->taskFinishCountField($taskerID));
		$this->Del($pkTaskID);
	}

	public function GetWorkerSendCount($workerID)
	{
		return $this->Get($this->workerSendCountField($workerID));
	}

	public function GetAllWorkerSendCount()
	{
		$res = [];
		for($workerID = 0; $workerID < $this->workerNum; $workerID++)
		{
			$res[$this->workerSendCountField($workerID)] = $this->GetWorkerSendCount($workerID);
		}

		return $res;
	}

	public function GetTaskerReceiveCount($taskWorkerID)
	{
		return $this->Get($this->taskReceiveCountField($taskWorkerID));
	}

	public function GetTaskerFinishCount($taskWorkerID)
	{
		return $this->Get($this->taskFinishCountField($taskWorkerID));
	}

	public function GetTaskFailedCount($taskWorkerID)
	{
		return $this->GetTaskerReceiveCount($taskWorkerID) - $this->GetTaskerFinishCount($taskWorkerID);
	}

	public function Set($key, $num = 0)
	{
		$this->table->set($key, [self::FIELD => $num]);
	}

	public function Get($key, $default = false)
	{
		$value = $this->table->get($key);

		return $value ? current($value) : $default;
	}

	public function Del($key)
	{
		return $this->table->del($key);
	}

	public function Incr($key, $incr = 1)
	{
		$this->table->incr($key, self::FIELD, $incr);
	}

	public function Decr($key, $decrby = 1)
	{
		$this->table->decr($key, $decrby);
	}

	protected function workerSendCountField($workerID)
	{
		return self::WORKER_SEND_COUNT_FIELD . '/' . $workerID;
	}

	protected function workerLastTaskIDField($workerID)
	{
		return self::WORKER_LAST_TASK_ID_FIELD . '/' . $workerID;
	}

	protected function taskReceiveCountField($taskWorkerID)
	{
		return self::TASK_RECEIVE_COUNT_FIELD . '/' . $taskWorkerID;
	}

	protected function taskFinishCountField($taskWorkerID)
	{
		return self::TASK_FINISH_COUNT_FIELD . '/' . $taskWorkerID;
	}

	protected function taskFailedCountField($taskWorkerID)
	{
		return self::TASK_FAILED_COUNT_FIELD . '/' . $taskWorkerID;
	}
}