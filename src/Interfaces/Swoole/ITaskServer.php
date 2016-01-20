<?php

namespace DIServer\Interfaces\Swoole;

/**
 * @author Back
 */
interface ITaskServer
{

	/**
	 * 进程启动时被触发
	 *
	 * @param \swoole_server $server    当前进程的swoole_server对象
	 * @param int            $worker_id 当前进程的ID
	 */
	public function OnTaskWorkerStart(\swoole_server $server, $task_worker_id);

	/**
	 * 进程发生错误时导致退出时触发（一般情况下，Manager会重新拉起一起新进程）
	 *
	 * @param \swoole_server $server     当前进程的swoole_server对象
	 * @param int            $worker_id  故障进程的ID
	 * @param int            $worker_pid 故障进程的PID
	 * @param int            $exit_code  错误代码
	 */
	public function OnTaskWorkerError(\swoole_server $server, $task_worker_id, $task_worker_pid, $exit_code);

	/**
	 * 进程正常结束时触发
	 *
	 * @param \swoole_server $server    当前进程的swoole_server对象
	 * @param int            $worker_id 当前进程的ID
	 */
	public function OnTaskWorkerStop(\swoole_server $server, $task_worker_id);

	/**
	 * TaskWorker收到任务时触发
	 *
	 * @param \swoole_server $server
	 * @param int            $task_id
	 * @param int            $from_id
	 * @param mixed          $param
	 */
	public function OnTask(\swoole_server $server, $task_id, $from_id, $param);

	/**
	 * 当工作进程收到由sendMessage发送的管道消息时会触发onPipeMessage
	 *
	 * @param \swoole_server $server
	 * @param int            $from_worker_id
	 * @param string         $message
	 */
	public function OnPipeMessage(\swoole_server $server, $from_worker_id, $message);
}
