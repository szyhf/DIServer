<?php

namespace DIServer\Interfaces;


interface IMonitor
{
	/**
	 * @return \swoole_table
	 */
	public function All();

	//public function OnTaskSend($workerID, $taskWorkerID);
	//
	//public function OnTask(\swoole_server $server, $task_id, $from_id, $param);
	//
	//public function OnTaskFinish($taskWorkerID);

	public function GetWorkerSendCount($workerID);

	public function GetTaskWorkerReceiveCount($taskWorkerID);

	public function GetTaskWorkerFinishCount($taskWorkerID);

	public function GetTaskFailedCount($taskWorkerID);
}