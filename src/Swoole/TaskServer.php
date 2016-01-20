<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer\Swoole;

use DIServer\Interfaces\Swoole\ITaskServer as ITaskServer;
use DIServer\Services\Log;
use DIServer\Services\Service;

/**
 * Description of TaskServer
 *
 * @author Back
 */
class TaskServer extends Service implements ITaskServer
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
		//$workerStrapps = include $this->getApp()
		//                              ->GetFrameworkPath() . '/Config/Worker.php';
		//foreach($workerStrapps as $iface => $imp)
		//{
		//	try
		//	{
		//		$this->getApp()
		//		     ->RegisterClass($imp);
		//		$this->getApp()
		//		     ->RegisterInterfaceByClass($iface, $imp);
		//	}
		//	catch(\Exception $ex)
		//	{
		//		Log::Instance()
		//		   ->Warning("Register taskworkerstrap[{$iface}=>{$imp}] failed.");
		//	}
		//}
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

	}

	/**
	 * 进程正常结束时触发
	 *
	 * @param \swoole_server $server    当前进程的swoole_server对象
	 * @param int            $worker_id 当前进程的ID
	 */
	public function OnTaskWorkerStop(\swoole_server $server, $task_worker_id)
	{
		Log::Notice("TaskWorker[$task_worker_id] stop");
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
		echo "Task accept." . $param . PHP_EOL;
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
		Log::Notice('On task Pipe Message');
	}
}
