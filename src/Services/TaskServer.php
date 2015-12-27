<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer\Services;

use DIServer\Interfaces\ITaskServer as ITaskServer;

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
		echo("TaskWorker[$task_worker_id] start" . PHP_EOL);
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
		echo("TaskWorker[$task_worker_id] Stop" . PHP_EOL);
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

	}

}
