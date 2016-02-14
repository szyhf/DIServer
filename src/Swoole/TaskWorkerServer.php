<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer\Swoole;

use DIServer\Interfaces\Swoole\ITaskWorkerServer as ITaskWorkerServer;
use DIServer\Services\Application;
use DIServer\Services\Event;
use DIServer\Services\Log;
use DIServer\Services\Service;

/**
 * Description of TaskServer
 *
 * @author Back
 */
class TaskWorkerServer extends Service implements ITaskWorkerServer
{

	/**
	 * 进程启动时被触发
	 *
	 * @param \swoole_server $server    当前进程的swoole_server对象
	 * @param int            $worker_id 当前进程的ID
	 */
	public function OnTaskWorkerStart(\swoole_server $server, $task_worker_id)
	{
		Log::Notice("On Task Worker[$task_worker_id] Start.");
		if(file_exists(Application::GetServerPath() . '/Registry/TaskWorker.php'))
		{
			$registry = include Application::GetServerPath() . '/Registry/TaskWorker.php';
			Application::AutoRegistry($registry);
		}
		Event::Listen('OnTaskWorkerStart', [&$server, &$task_worker_id]);
	}

	/**
	 * 进程发生错误时导致退出时触发（一般情况下，Manager会重新拉起一起新进程）
	 *
	 * @param \swoole_server $server     当前进程的swoole_server对象
	 * @param int            $worker_id  故障进程的ID
	 * @param int            $worker_pid 故障进程的PID
	 * @param int            $exit_code  错误代码
	 */
	public function OnTaskWorkerError(\swoole_server $server, $task_worker_id, $task_worker_pid, $exit_code)
	{
		Log::Error("On TaskWorker Error Exit, task_worker_id = $task_worker_id, task_worker_pid = $task_worker_pid, exit_code=$exit_code.");
	}

	/**
	 * 进程正常结束时触发
	 *
	 * @param \swoole_server $server    当前进程的swoole_server对象
	 * @param int            $worker_id 当前进程的ID
	 */
	public function OnTaskWorkerStop(\swoole_server $server, $task_worker_id)
	{
		Log::Notice("On TaskWorker[$task_worker_id] stop");
	}

	/**
	 * TaskWorker收到任务时触发
	 *
	 * @param \swoole_server $server
	 * @param int            $task_id
	 * @param int            $from_id
	 * @param mixed          $param
	 */
	public function OnTask(\swoole_server $server, $task_id, $from_id, $param)
	{
		//Log::Debug("Task accept task_id[$task_id]  from_id[$from_id]");
		Event::Listen('TaskReceived', [&$server, &$task_id, &$from_id, &$param]);
		//if($server->worker_id % 2 == 0)
		//{
		//	sleep(2);
		//	if(rand(0, 100) > 50)
		//	{
		//		//return;
		//	}
		//}
		Event::Listen('TaskFinished',[&$server, &$task_id, &$from_id, &$param]);
		$server->finish('finish');
	}

	/**
	 * 当工作进程收到由sendMessage发送的管道消息时会触发onPipeMessage
	 *
	 * @param \swoole_server $server
	 * @param int            $from_worker_id
	 * @param string         $message
	 */
	public function OnPipeMessage(\swoole_server $server, $from_worker_id, $message)
	{
		Log::Debug("Receive message from $from_worker_id in $server->worker_id.");
	}
}
