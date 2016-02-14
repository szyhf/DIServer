<?php

namespace DIServer\Request;

use DIServer\Interfaces\IRequest;
use DIServer\Services\Container;

class Base implements IRequest
{
	protected $fd;
	protected $fromID;
	protected $data;
	protected $remoteIP;
	protected $remotePort;
	protected $serverPort;
	protected $connectTime;
	protected $lastTime;

	protected function tryInitInfo($force = false)
	{
		if(!isset($this->remoteIP) || $force)
		{
			//因为跨进程投递时server会改变，为了确保成功，不使用构造函数注入的方式获取当前server
			/** @var \swoole_server $swoole */
			$swoole = Container::GetInstance(\swoole_server::class);
			$connectionInfo = $swoole->connection_info($this->GetFD(), $this->GetFromID());
			$this->remoteIP = $connectionInfo['remote_ip'];
			$this->remotePort = $connectionInfo['remote_port'];
			$this->serverPort = $connectionInfo['server_port'];
			$this->connectTime = $connectionInfo['connect_time'];
			$this->lastTime = $connectionInfo['last_time'];
		}
	}

	public function __construct($fd, $fromID, $data)
	{
		$this->fd = $fd;
		$this->fromID = $fromID;
		$this->data = $data;
	}

	public function GetFD()
	{
		return $this->fd;
	}

	public function GetFromID()
	{
		return $this->fromID;
	}

	public function GetData()
	{
		return $this->data;
	}

	public function GetRemoteIP()
	{
		$this->tryInitInfo();

		return $this->remoteIP;
	}

	public function GetServerPort()
	{
		$this->tryInitInfo();

		return $this->serverPort;
	}

	public function GetRemotePort()
	{
		$this->tryInitInfo();

		return $this->remotePort;
	}

	public function GetConnectTime()
	{
		$this->tryInitInfo();

		return $this->connectTime;
	}

	public function GetLastTime()
	{
		$this->tryInitInfo(true);

		return $this->lastTime;
	}


}