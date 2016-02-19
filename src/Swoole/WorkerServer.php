<?php
namespace DIServer\Swoole;

use DIServer\Interfaces\Swoole\IWorkerServer;
use DIServer\Interfaces\IRequest;
use DIServer\Services\Application;
use DIServer\Services\Dispatcher;
use DIServer\Services\Event;
use DIServer\Services\Log;
use DIServer\Services\RequestFactory;
use DIServer\Services\Service;

/**
 * Description of WorkerServer
 *
 * @author Back
 */
class WorkerServer extends Service implements IWorkerServer
{
	/**
	 * 新建了一个Tcp连接时触发
	 *
	 * @param \swoole_server $server  当前进程的swoole_server对象
	 * @param int            $fd      当前连接的文件描述符（惟一）
	 * @param int            $from_id 当前连接的Rector线程
	 */
	public function OnConnect(\swoole_server $server, $fd, $from_id)
	{
		$connectInfo = $server->connection_info($fd, $from_id);
		Log::Info("Connect from {remote_ip}[$fd] to worker[$server->worker_id]", $connectInfo);
	}

	/**
	 * 关闭了一个Tcp连接时触发
	 *
	 * @param \swoole_server $server  当前进程的swoole_server对象
	 * @param int            $fd      当前连接的文件描述符（惟一）
	 * @param int            $from_id 当前连接的Rector线程
	 */
	public function OnClose(\swoole_server $server, $fd, $from_id)
	{
		$connectInfo = $server->connection_info($fd, $from_id);
		Log::Info("Close from {remote_ip}[$fd] to worker[$server->worker_id]", $connectInfo);
	}

	/**
	 * 接受到一个Tcp客户端发来的数据时触发
	 *
	 * @param \swoole_server $server  当前进程的swoole_server对象
	 * @param int            $fd      当前连接的文件描述符（惟一）
	 * @param int            $from_id 当前连接的Rector线程
	 * @param string         $data    接收到的数据（如果没有设置包头\拆包协议，可能收到的数据会不完整或者黏包）
	 */
	public function OnReceive(\swoole_server $server, $fd, $from_id, $data)
	{
		/** @var IRequest $request */
		$request = RequestFactory::Make($fd, $from_id, $data);
		Event::Listen('OnRequest',[&$server,&$request]);
		Dispatcher::Dispatch($request);
	}

	/**
	 * 接收到一个Udp数据包时被触发
	 *
	 * @param \swoole_server $server      当前进程的swoole_server对象
	 * @param string         $data        接受到的数据包
	 * @param array          $client_info 客户端信息
	 */
	public function OnPacket(\swoole_server $server, $data, $client_info)
	{

	}

	/**
	 * 进程启动时被触发
	 *
	 * @param \swoole_server $server    当前进程的swoole_server对象
	 * @param int            $worker_id 当前进程的ID
	 */
	public function OnWorkerStart(\swoole_server $server, $worker_id)
	{
		Log::Notice("On Worker[$worker_id] Start.");
	}

	/**
	 * 进程发生错误时导致退出时触发（一般情况下，Manager会重新拉起一起新进程）
	 *
	 * @param \swoole_server $server     当前进程的swoole_server对象
	 * @param int            $worker_id  故障进程的ID
	 * @param int            $worker_pid 故障进程的PID
	 * @param int            $exit_code  错误代码
	 */
	public function OnWorkerError(\swoole_server $server, $worker_id, $worker_pid, $exit_code)
	{

	}

	/**
	 * 进程正常结束时触发
	 *
	 * @param \swoole_server $server    当前进程的swoole_server对象
	 * @param type           $worker_id 当前进程的ID
	 */
	public function OnWorkerStop(\swoole_server $server, $worker_id)
	{
		Log::Notice("On Worker[$worker_id] Stop.");
	}

	/**
	 * 当工作进程收到由sendMessage发送的管道消息时会触发onPipeMessage
	 *
	 * @param \swoole_server $server
	 * @param int            $from_worker_id
	 * @param string         $message
	 */
	public function OnWorkerPipeMessage(\swoole_server $server, $from_worker_id, $message)
	{
		Log::Debug("Receive message from $from_worker_id in $server->worker_id.");
		/** @var \swoole_table $table */
		//$table = unserialize($message);
	}

	/**
	 * Task一次工作结束以后，在Worker进程中被触发（如果在Task中执行了Finish方法）
	 *
	 * @param \swoole_server $server     当前进程的swoole_server对象
	 * @param int            $task_id    结束工作的Task的ID
	 * @param mixed          $taskResult 在OnTask方法中被Return的数据。
	 */
	public function OnFinish(\swoole_server $server, $task_id, $taskResult)
	{

	}
}