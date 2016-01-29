<?php
namespace DIServer\Swoole;

use DIServer\Interfaces\Swoole\IManagerServer as IManagerServer;
use DIServer\Interfaces\Swoole\IMasterServer as IMasterServer;
use DIServer\Interfaces\Swoole\ITaskWorkerServer as ITaskWorkerServer;
use DIServer\Interfaces\Swoole\IWorkerServer as IWorkerServer;
use DIServer\Interfaces\Swoole\ISwooleProxy;
use DIServer\Services\Service;

/**
 * 根据进程拆分Swoole的回调
 *
 * @author Back
 */
class SwooleProxy extends Service implements ISwooleProxy
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

	public function __construct(\DIServer\Interfaces\IApplication $app, \swoole_server $server)
	{
		parent::__construct($app);
		$server->on("start", [$this, 'OnStart']);
		$server->on("connect", [$this, 'OnConnect']);
		$server->on("receive", [$this, 'OnReceive']);
		$server->on("close", [$this, 'OnClose']);
		$server->on("task", [$this, 'OnTask']);
		$server->on('finish', [$this, 'OnFinish']);
		$server->on('shutdown', [$this, 'OnShutdown']);
		$server->on('WorkerStart', [$this, 'OnWorkerStart']);
		$server->on('WorkerStop', [$this, 'OnWorkerStop']);
		$server->on('WorkerError', [$this, 'OnWorkerError']);
		$server->on('PipeMessage', [$this, 'OnPipeMessage']);
		$server->on('ManagerStart', [$this, 'OnManagerStart']);
		$server->on('ManagerStop', [$this, 'OnManagerStop']);
		$server->on('Packet', [$this, 'OnPacket']);
	}

	public function OnStart(\swoole_server $server)
	{
		$this->getApp()
		     ->Unregister(\swoole_server::class);
		$this->getApp()
		     ->RegisterClassByInstance(\swoole_server::class, $server);
		$this->masterServer = $this->getApp()
		                           ->GetInstance(IMasterServer::class);
		$this->masterServer->OnStart($server);
	}

	public function OnShutdown(\swoole_server $server)
	{
		$this->masterServer->OnShutdown($server);
	}

	public function OnClose($server, $fd, $from_id)
	{
		$this->workerServer->OnClose($server, $fd, $from_id);
	}

	public function OnConnect(\swoole_server $server, $fd, $from_id)
	{
		$this->workerServer->OnConnect($server, $fd, $from_id);
	}

	public function OnFinish(\swoole_server $server, $task_id, $taskResult)
	{
		$this->workerServer->OnFinish($server, $task_id, $taskResult);
	}

	public function OnPacket(\swoole_server &$server, $data, $client_info)
	{
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
			$this->taskerServer->OnPipeMessage($server, $from_worker_id, $message);
		}
		else
		{
			$this->workerServer->OnPipeMessage($server, $from_worker_id, $message);
		}
	}

	public function OnReceive(\swoole_server $server, $fd, $from_id, $data)
	{
		$this->workerServer->OnReceive($server, $fd, $from_id, $data);
	}

	public function OnTask(\swoole_server $server, $task_id, $from_id, $param)
	{
		$this->taskerServer->OnTask($server, $task_id, $from_id, $param);
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
			$this->taskerServer = $this->getApp()
			                           ->GetInstance(ITaskWorkerServer::class);
			$this->taskerServer->OnTaskerError($server, $worker_id, $worker_pid, $exit_code);
		}
		else
		{
			$this->workerServer = $this->getApp()
			                           ->GetInstance(IWorkerServer::class);
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
		//各个进程的$server对象不是同一个，要重置。
		$this->getApp()
		     ->Unregister(\swoole_server::class);
		$this->getApp()
		     ->RegisterClassByInstance(\swoole_server::class, $server);
		$reloadConfig = include $this->getApp()
		                             ->GetFrameworkPath() . '/Registry/ServerReload.php';
		$this->getApp()
		     ->AutoRegistry($reloadConfig);
		if($server->taskworker)
		{
			$this->taskerServer = $this->getApp()
			                           ->GetInstance(ITaskWorkerServer::class);
			$this->taskerServer->OnTaskWorkerStart($server, $worker_id);
		}
		else
		{
			$this->workerServer = $this->getApp()
			                           ->GetInstance(IWorkerServer::class);
			$this->workerServer->OnWorkerStart($server, $worker_id);
		}
	}

	public function OnWorkerStop(\swoole_server $server, $worker_id)
	{
		if($server->taskworker)
		{
			$this->taskerServer = $this->getApp()
			                           ->GetInstance(ITaskWorkerServer::class);
			$this->taskerServer->OnTaskWorkerStop($server, $worker_id);
		}
		else
		{
			$this->workerServer = $this->getApp()
			                           ->GetInstance(IWorkerServer::class);
			$this->workerServer->OnWorkerStop($server, $worker_id);
		}
	}

	public function OnManagerStart(\swoole_server $server)
	{
		//各个进程的$server对象不是同一个，要重置。
		$this->getApp()
		     ->Unregister(\swoole_server::class);
		$this->getApp()
		     ->RegisterClassByInstance(\swoole_server::class, $server);
		$this->managerServer = $this->getApp()
		                            ->GetInstance(IManagerServer::class);
		$this->managerServer->OnManagerStart($server);
	}

	public function OnManagerStop(\swoole_server $server)
	{
		$this->managerServer->OnManagerStop($server);
	}
}
