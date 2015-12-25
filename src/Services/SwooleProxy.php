<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer\Services;

use \DIServer\Interfaces\ISwooleProxy as ISwooleProxy;
use \DIServer\Services\Service as Service;

/**
 * 根据进程拆分Swoole的回调
 *
 * @author Back
 */
class SwooleProxy extends Service implements ISwooleProxy
{

    /**
     * 主进程服务
     * @var \DIServer\Interfaces\IMasterServer 
     */
    protected $masterServer;

    /**
     * 管理进程服务
     * @var \DIServer\Interfaces\IManagerServer 
     */
    protected $managerServer;

    /**
     * Worker进程服务
     * @var \DIServer\Interfaces\IWorkerServer 
     */
    protected $workerServer;

    /**
     * Task进程服务
     * @var \DIServer\Interfaces\ITaskServer 
     */
    protected $taskServer;

    public function Register()
    {
	parent::Register();
	$ioc = $this->App()->IOC();
	/* @var $server \swoole_server */
	$server = $ioc->GetInstance(\swoole_server::class);
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
	$this->App()->IOC()->Unregister(\swoole_server::class);
	$this->App()->IOC()->RegisterClassByInstance(\swoole_server::class, $server);
	$this->masterServer = $this->App()->IOC()->GetInstance(\DIServer\Interfaces\IMasterServer::class);
	$this->masterServer->OnServerStart($server);
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

    public function OnPipeMessage(\swoole_server $server, $from_worker_id, $message)
    {
	$this->workerServer->OnPipeMessage($server, $from_worker_id, $message);
    }

    public function OnReceive(\swoole_server $server, $fd, $from_id, $data)
    {
	$this->workerServer->OnReceive($server, $fd, $from_id, $data);
    }

    public function OnTask(\swoole_server $server, $task_id, $from_id, $param)
    {
	$this->taskServer->OnTask($server, $task_id, $from_id, $param);
    }

    public function OnWorkerError(\swoole_server $serv, $worker_id, $worker_pid, $exit_code)
    {
	if ($server->taskworker)
	{
	    $this->taskServer = $this->App()->IOC()->GetInstance(\DIServer\Interfaces\ITaskServer::class);
	    $this->taskServer->OnTaskWorkerError($server, $task_worker_id);
	}
	else
	{
	    $this->workerServer = $this->App()->IOC()->GetInstance(\DIServer\Interfaces\IWorkerServer::class);
	    $this->workerServer->OnWorkerError($server, $worker_id);
	}
    }

    public function OnWorkerStart(\swoole_server $server, $worker_id)
    {
	//各个进程的$server对象不是同一个，要重置。
	$this->App()->IOC()->Unregister(\swoole_server::class);
	$this->App()->IOC()->RegisterClassByInstance(\swoole_server::class, $server);
	if ($server->taskworker)
	{
	    $this->taskServer = $this->App()->IOC()->GetInstance(\DIServer\Interfaces\ITaskServer::class);
	    $this->taskServer->OnTaskWorkerStart($server, $task_worker_id);
	}
	else
	{
	    $this->workerServer = $this->App()->IOC()->GetInstance(\DIServer\Interfaces\IWorkerServer::class);
	    $this->workerServer->OnWorkerStart($server, $worker_id);
	}
    }

    public function OnWorkerStop(\swoole_server $server, $worker_id)
    {
	if ($server->taskworker)
	{
	    $this->taskServer = $this->App()->IOC()->GetInstance(\DIServer\Interfaces\ITaskServer::class);
	    $this->taskServer->OnTaskWorkerStop($server, $task_worker_id);
	}
	else
	{
	    $this->workerServer = $this->App()->IOC()->GetInstance(\DIServer\Interfaces\IWorkerServer::class);
	    $this->workerServer->OnWorkerStop($server, $worker_id);
	}
    }

    public function OnManagerStart(\swoole_server $server)
    {
	//各个进程的$server对象不是同一个，要重置。
	$this->App()->IOC()->Unregister(\swoole_server::class);
	$this->App()->IOC()->RegisterClassByInstance(\swoole_server::class, $server);
	$this->managerServer->OnManagerStart($server);
    }

    public function OnManagerStop(\swoole_server $server)
    {
	$this->managerServer->OnManagerStop($server);
    }

}
