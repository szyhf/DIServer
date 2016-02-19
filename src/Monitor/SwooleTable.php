<?php

namespace DIServer\Monitor;


use DIServer\Interfaces\IMonitor;
use DIServer\Interfaces\IRequest;
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
	const REQUEST_NUM = 'Request Num';//5;
	const WORKER_REQUEST_NUM = 'Worker Request Num';//6;
	const WORKER_SEND_COUNT_FIELD = 'Worker Sent';//8;
	const WORKER_LAST_TASK_ID_FIELD = 'TaskWorker Last Task';//9;
	const TASK_RECEIVE_COUNT_FIELD = 'Task Received';//11;
	const TASK_FINISH_COUNT_FIELD = 'Task Finished';//12;
	const WORKER_FINISH_TASK_FIELD = "Worker Finished Task";
	const TASK_WORKER_LAST_TASK_FIELD = 'TaskWorker Last Task';
	const TASK_WORKER_LAST_WORKER_FIELD = 'TaskWorker Last Worker';


	public function __construct(\swoole_server $server)
	{
		$defaultRow = 1;//1个基本统计量
		$rowPerWorker = 2;
		$rowPerTaskWorker = 4;//3个固定位置加1个记录pkTask的位置
		$this->server = $server;
		$workerNum = $server->setting['worker_num'];
		$taskNum = $server->setting['task_worker_num'];
		$rowCount = $defaultRow + $workerNum * $rowPerWorker + $taskNum * $rowPerTaskWorker;
		$rowCount = log10($rowCount) / log10(2);//以2为底求需要的最小倍数
		$rowCount = (int)($rowCount + 1);//即便没有余数也+1取整保证留有一定的余地
		$rowCount = pow(2, $rowCount);//获得最接近的行数
		//Log::Debug("Monitor row count set to $rowCount");
		$this->table = new \swoole_table($rowCount);

		$this->table->column(self::FIELD, \swoole_table::TYPE_INT);
		$this->table->create();
		//初始化默认字段
		$this->Set(self::REQUEST_NUM);
		for($i = 0; $i < $workerNum; $i++)
		{
			$this->Set($this->workerRequsetNum($i));
			$this->Set($this->workerSendCountField($i));
		}
		for($i = 0; $i < $taskNum; $i++)
		{
			$taskWorkerID = $i + $workerNum;
			$this->Set($this->taskReceiveCountField($taskWorkerID));
			$this->Set($this->taskWorkerLastWorkerIDField($taskWorkerID));
			$this->Set($this->taskWorkerLastTaskIDField($taskWorkerID));
		}

		$this->taskNum = $taskNum;
		$this->workerNum = $workerNum;
	}

	public function Bind()
	{
		Event::Add('TaskReceived', [$this, 'TaskReceived']);
		Event::Add("TaskFinished", [$this, 'TaskFinished']);
		Event::Add('OnFinish', [$this, 'OnFinish']);
		Event::Add('TaskSent', [$this, 'TaskSent']);
		Event::Add('OnRequest', [$this, 'OnRequest']);
	}

	public function OnRequest(\swoole_server $server, IRequest $request)
	{
		$this->Incr(self::REQUEST_NUM);
		$this->Incr($this->workerRequsetNum($server->worker_id));
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
		$TaskingNum = 0;
		$workers = [];
		for($i = 0; $i < $this->workerNum; $i++)
		{
			$workers[$i]["ReceiveRequestNum"] = $this->GetWorkerSendCount($i);
			$workers[$i]["SendTaskCount"] = $this->GetWorkerSendCount($i);
			$workers[$i]["FinishedTaskCount"] = $this->GetWorkerFinishedTaskCount($i);
			$workers[$i]["TaskingNum"] = $workers[$i]["SendTaskCount"] - $workers[$i]["FinishedTaskCount"];
		}
		//$base["TaskingNum"] = $TaskingNum;
		$base["Worker"] = $workers;
		$taskWorkers = [];
		for($i = 0; $i < $this->taskNum; $i++)
		{
			$taskWorkerID = $this->workerNum + $i;
			$taskWorkers[$taskWorkerID]['ReceivedCount'] = $this->GetTaskWorkerReceiveCount($taskWorkerID);
			$taskWorkers[$taskWorkerID]['FinishedCount'] = $this->GetTaskWorkerFinishCount($taskWorkerID);
			$failedCount = $taskWorkers[$taskWorkerID]['ReceivedCount'] - $taskWorkers[$taskWorkerID]['FinishedCount'] - 1;
			$taskWorkers[$taskWorkerID]['FailedCount'] = $failedCount > 0 ? $failedCount : 0;
			//$base["Worker[$i]LastTaskerID"]=>$this->GEt
		}
		$base['TaskWorker'] = $taskWorkers;
		$base['MonitorRowCount'] = count($this->table);
		//foreach($this->table as $key => $row)
		//{
		//	$base['MonitorKeys'][$key] = $row['Num'];
		//}
		return $base;
	}

	public function TaskSent($task, $taskID)
	{
		//在Worker进程调用Task或者TaskWait的时候计数
		$this->Incr($this->workerSendCountField($this->server->worker_id));
	}

	public function TaskReceived(\swoole_server $server, $task_id, $from_id, $param)
	{
		$currentTaskWorkerID = $server->worker_id;
		//在Task进程触发TaskReceived的时候计数
		$this->Incr($this->taskReceiveCountField($currentTaskWorkerID));
	}

	public function TaskFinished(\swoole_server $server, $task_id, $from_id, $param)
	{
		//记录指定的TaskWorker完成了的任务数
		$this->Incr($this->taskFinishCountField($server->worker_id));
		//记录当前Worker投递后被完成的任务数(放在OnFinish回调中会导致内存泄漏）
		$this->Incr($this->workerFinishedTaskField($from_id));
	}

	public function OnFinish(\swoole_server $server, $task_id, $taskResult)
	{
		$currentWorkerID = $server->worker_id;
		//记录当前Worker投递后被完成的任务数
		$this->Incr($this->workerFinishedTaskField($currentWorkerID));
	}

	public function GetWorkerSendCount($workerID)
	{
		return $this->Get($this->workerSendCountField($workerID));
	}

	public function GetWorkerFinishedTaskCount($workerID)
	{
		return $this->Get($this->workerFinishedTaskField($workerID));
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

	public function GetTaskWorkerReceiveCount($taskWorkerID)
	{
		return $this->Get($this->taskReceiveCountField($taskWorkerID));
	}

	public function GetTaskWorkerFinishCount($taskWorkerID)
	{
		return $this->Get($this->taskFinishCountField($taskWorkerID));
	}

	public function GetTaskFailedCount($taskWorkerID)
	{
		return $this->GetTaskWorkerReceiveCount($taskWorkerID) - $this->GetTaskWorkerFinishCount($taskWorkerID);
	}

	public function Set($key, $num = 0)
	{
		//Log::Debug("Set $key to $num");
		return $this->table->set($key, [self::FIELD => $num]);
	}

	public function Get($key, $default = 0)
	{
		$value = $this->table->get($key);

		return $value ? current($value) : $default;
	}

	public function Del($key)
	{
		//Log::Debug("Del $key");
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

	protected function pkTaskID($workerID, $taskID)
	{
		return "$workerID+$taskID";
	}

	protected function workerSendCountField($workerID)
	{
		return self::WORKER_SEND_COUNT_FIELD . '/' . $workerID;
	}

	protected function taskWorkerLastTaskIDField($taskID)
	{
		return self::TASK_WORKER_LAST_TASK_FIELD . '/' . $taskID;
	}

	protected function taskWorkerLastWorkerIDField($workerID)
	{
		return self::TASK_WORKER_LAST_WORKER_FIELD . '/' . $workerID;
	}

	protected function taskReceiveCountField($taskWorkerID)
	{
		return self::TASK_RECEIVE_COUNT_FIELD . '/' . $taskWorkerID;
	}

	protected function taskFinishCountField($taskWorkerID)
	{
		return self::TASK_FINISH_COUNT_FIELD . '/' . $taskWorkerID;
	}

	protected function workerFinishedTaskField($workerID)
	{
		return self::WORKER_FINISH_TASK_FIELD . '/' . $workerID;
	}

	protected function workerRequsetNum($workerID)
	{
		return self::WORKER_REQUEST_NUM . '/' . $workerID;
	}

}