<?php
namespace DIServer\Swoole;

use DIServer\Interfaces\IApplication;
use DIServer\Interfaces\Swoole\IManagerServer;
use DIServer\Interfaces\Swoole\IMasterServer;
use DIServer\Interfaces\Swoole\ITaskWorkerServer;
use DIServer\Interfaces\Swoole\IWorkerServer;
use DIServer\Interfaces\Swoole\ISwooleProxy;
use DIServer\Services\Application;
use DIServer\Services\Container;
use DIServer\Services\Log;
use DIServer\Services\Event;

/**
 * 根据进程拆分Swoole的回调
 *
 * @author Back
 */
class SwooleProxy implements ISwooleProxy
{

	/**
	 * 主进程服务
	 *
	 * @var \DIServer\Interfaces\Swoole\IMasterServer
	 */
	protected $masterServer;

	/**
	 * 管理进程服务
	 *
	 * @var \DIServer\Interfaces\Swoole\IManagerServer
	 */
	protected $managerServer;

	/**
	 * Worker进程服务
	 *
	 * @var \DIServer\Interfaces\Swoole\IWorkerServer
	 */
	protected $workerServer;

	/**
	 * Task进程服务
	 *
	 * @var \DIServer\Interfaces\Swoole\ITaskWorkerServer
	 */
	protected $taskerServer;

	public function __construct(\swoole_server $server)
	{
		$server->on("start", [$this, 'OnMasterStart']);
		$server->on("connect", [$this, 'OnConnect']);
		$server->on("receive", [$this, 'OnReceive']);
		$server->on("close", [$this, 'OnClose']);
		$server->on("task", [$this, 'OnTask']);
		$server->on('finish', [$this, 'OnFinish']);
		$server->on('shutdown', [$this, 'OnMasterShutdown']);
		$server->on('WorkerStart', [$this, 'OnWorkerStart']);
		$server->on('WorkerStop', [$this, 'OnWorkerStop']);
		$server->on('WorkerError', [$this, 'OnWorkerError']);
		$server->on('PipeMessage', [$this, 'OnPipeMessage']);
		$server->on('ManagerStart', [$this, 'OnManagerStart']);
		$server->on('ManagerStop', [$this, 'OnManagerStop']);
		$server->on('Packet', [$this, 'OnPacket']);
	}

	public function OnMasterStart(\swoole_server $server)
	{
		Container::Unregister(\swoole_server::class);
		Container::RegisterClassByInstance(\swoole_server::class, $server);
		$this->masterServer = Container::GetInstance(IMasterServer::class);
		Event::Listen('OnMasterStart', [&$server]);
		$this->masterServer->OnMasterStart($server);
	}

	public function OnMasterShutdown(\swoole_server $server)
	{
		Event::Listen('OnMasterShutdown', [&$server]);
		$this->masterServer->OnMasterShutdown($server);
	}

	public function OnClose($server, $fd, $from_id)
	{
		Event::Listen('OnClose', [&$server, &$fd, &$from_id]);
		$this->workerServer->OnClose($server, $fd, $from_id);
	}

	public function OnConnect(\swoole_server $server, $fd, $from_id)
	{
		Event::Listen('OnConnect', [&$server, &$fd, &$from_id]);
		$this->workerServer->OnConnect($server, $fd, $from_id);
	}

	public function OnFinish(\swoole_server $server, $task_id, $taskResult)
	{
		Event::Listen('OnFinish', [&$server, &$task_id, &$taskResult]);
		$this->workerServer->OnFinish($server, $task_id, $taskResult);
	}

	public function OnPacket(\swoole_server &$server, $data, $client_info)
	{
		Event::Listen('OnPacket', [&$server, &$data, $client_info]);
		$this->workerServer->OnPacket($server, $data, $client_info);
	}

	/**
	 * 工作进程间通讯回调（划分TaskWorker进程和Worker进程）
	 *
	 * @param \swoole_server $server
	 * @param                $from_worker_id
	 * @param                $message
	 */
	public function OnPipeMessage(\swoole_server $server, $from_worker_id, $message)
	{
		if($server->taskworker)
		{
			Event::Listen('OnTaskWorkerPipeMessage', [&$server, &$from_worker_id, &$message]);
			$this->taskerServer->OnTaskWorkerPipeMessage($server, $from_worker_id, $message);
		}
		else
		{
			Event::Listen('OnWorkerPipeMessage', [&$server, &$from_worker_id, &$message]);
			$this->workerServer->OnWorkerPipeMessage($server, $from_worker_id, $message);
		}
	}

	public function OnReceive(\swoole_server $server, $fd, $from_id, $data)
	{
		try
		{
			Event::Listen('OnReceive', [&$server, &$fd, &$from_id, &$data]);
			//尽量避免Worker直接挂掉。
			$this->workerServer->OnReceive($server, $fd, $from_id, $data);
		}
		catch(\Exception $receiveException)
		{
			Log::Error("Catch exception in receive working:{$receiveException->getMessage()}");
		}

	}

	public function OnTask(\swoole_server $server, $task_id, $from_id, $param)
	{
		try
		{
			Event::Listen('OnTaskReceived', [&$server, &$task_id, &$from_id, &$param]);
			//尽量避免TaskWorker直接挂掉。
			$this->taskerServer->OnTask($server, $task_id, $from_id, $param);
			Event::Listen('OnTaskFinished', [&$server, &$task_id, &$from_id, &$param]);
		}
		catch(\Exception $taskException)
		{
			Log::Error("Catch exception in task working:{$taskException->getMessage()}");
		}
	}

	/**
	 * 工作进程异常的代理（划分TaskWorker进程和Worker进程）
	 *
	 * @param \swoole_server $server     当前服务
	 * @param int            $worker_id  工作进程id
	 * @param int            $worker_pid 工作进程pid
	 * @param int            $exit_code  错误代码
	 *
	 * @throws \DIServer\Container\NotRegistedException
	 */
	public function OnWorkerError(\swoole_server $server, $worker_id, $worker_pid, $exit_code)
	{
		if($server->taskworker)
		{
			$this->taskerServer = Container::GetInstance(ITaskWorkerServer::class);
			Event::Listen('OnTaskWorkerError', [&$server, &$worker_id, &$worker_pid, $exit_code]);
			$this->taskerServer->OnTaskerError($server, $worker_id, $worker_pid, $exit_code);
		}
		else
		{
			$this->workerServer = Container::GetInstance(IWorkerServer::class);
			Event::Listen('OnWorkerError', [&$server, &$worker_id, &$worker_pid, &$exit_code]);
			$this->workerServer->OnWorkerError($server, $worker_id, $worker_pid, $exit_code);
		}
	}

	/**
	 * Worker进程启动时触发并划分普通worker进程和task进程
	 *
	 * @param \swoole_server $server
	 * @param int            $worker_id
	 *
	 * @throws \DIServer\Container\NotRegistedException
	 * @throws \DIServer\Container\NotTypeOfInstanceException
	 */
	public function OnWorkerStart(\swoole_server $server, $worker_id)
	{
		//清理opcache
		opcache_reset();
		//各个进程的$server对象不是同一个，要重置。
		Container::Unregister(\swoole_server::class);
		Container::RegisterClassByInstance(\swoole_server::class, $server);
		Application::AutoRegistry('CommonWorker.php', true);
		if($server->taskworker)
		{
			$this->taskerServer = Container::GetInstance(ITaskWorkerServer::class);
			Application::AutoRegistry('TaskWorker.php');
			Event::Listen('OnTaskWorkerStart', [&$server, &$worker_id]);
			$this->taskerServer->OnTaskWorkerStart($server, $worker_id);
		}
		else
		{
			$this->workerServer = Container::GetInstance(IWorkerServer::class);
			Application::AutoRegistry('Worker.php');
			Event::Listen('OnWorkerStart', [&$server, &$worker_id]);
			$this->workerServer->OnWorkerStart($server, $worker_id);
		}
	}

	public function OnWorkerStop(\swoole_server $server, $worker_id)
	{
		if($server->taskworker)
		{
			$this->taskerServer = Container::GetInstance(ITaskWorkerServer::class);
			Event::Listen('OnTaskWorkerStop', [&$server, &$worker_id]);
			$this->taskerServer->OnTaskWorkerStop($server, $worker_id);
		}
		else
		{
			$this->workerServer = Container::GetInstance(IWorkerServer::class);
			Event::Listen('OnWorkerStop', [&$server, &$worker_id]);
			$this->workerServer->OnWorkerStop($server, $worker_id);
		}
	}

	public function OnManagerStart(\swoole_server $server)
	{
		//各个进程的$server对象不是同一个，要重置。
		Container::Unregister(\swoole_server::class);
		Container::RegisterClassByInstance(\swoole_server::class, $server);
		$this->managerServer = Container::GetInstance(IManagerServer::class);
		Event::Listen('OnManagerStart', [$server]);
		$this->managerServer->OnManagerStart($server);
	}

	public function OnManagerStop(\swoole_server $server)
	{
		Event::Listen('OnManagerStop', [$server]);
		$this->managerServer->OnManagerStop($server);
	}
}
