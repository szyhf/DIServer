<?php

namespace DIServer\Bootstraps;

use DIServer\Interfaces\IListener;
use DIServer\Services\Application;
use DIServer\Interfaces\Swoole\ISwooleProxy;
use DIServer\Services\Log;

/**
 * 初始化swooler_server，设置监听
 *
 * @author Back
 */
class InitSwooleServer extends Bootstrap
{
	/**
	 * @var \swoole_server
	 */
	protected $swoole;

	private $_listenerConfigs = [];

	public function Bootstrap()
	{
		/** @var IListener $defaultListener */
		$defaultListener = $this->initListener()
		                        ->current();
		//拿出第一个监听作为构造函数的参数
		$initParams = [
			'serv_host' => $defaultListener->GetHost(),
			'serv_port' => $defaultListener->GetPort(),
			'serv_mode' => SWOOLE_PROCESS,
			'sock_type' => $defaultListener->GetType(),
		];
		Application::RegisterClass(\swoole_server::class, $initParams);
		$this->swoole = Application::GetInstance(\swoole_server::class);

		//记录一下
		$this->_listenerConfigs[get_class($defaultListener)] = [
			'Host' => $defaultListener->GetHost(),
			'Port' => $defaultListener->GetPort(),
			'Type' => $this->_getTypeName($defaultListener->GetType())
		];

		//注册剩余的Listener
		$this->initListener()
		     ->send('');

		$this->initProxy();//构造时已经自动完成了回调注册

		Log::Notice(['Listeners' => $this->_listenerConfigs]);
	}

	protected function initListener()
	{
		$listeners = Application::AutoBuildCollection("Listener.php", IListener::class);

		yield array_shift($listeners);
		/** @var IListener $listener */
		foreach($listeners as $listener)
		{
			$port = $this->swoole->addlistener($listener->GetHost(), $listener->GetPort(), $listener->GetType());
			if(is_array($listener->GetSetting()))
			{
				$port->set($listener->GetSetting());
			}
			$this->_listenerConfigs[get_class($listener)] = [
				'Host' => $listener->GetHost(),
				'Port' => $listener->GetPort(),
				'Type' => $this->_getTypeName($listener->GetType())
			];
		}
	}

	protected function initProxy()
	{
		/** @var \DIServer\Interfaces\Swoole\ISwooleProxy $swooleProxy */
		Application::GetInstance(ISwooleProxy::class);
	}

	private function _getTypeName($type)
	{
		switch($type)
		{
			case SWOOLE_SOCK_TCP:
			{
				return "TCP";
			}
			case SWOOLE_SOCK_TCP6:
			{
				return "TCP6";
			}
			case SWOOLE_UDP:
			{
				return "UDP";
			}
			case SWOOLE_UDP6:
			{
				return "UDP6";
			}
			case SWOOLE_SOCK_UNIX_DGRAM:
			{
				return "UNIX_DGRAM";
			}
			case SWOOLE_SOCK_UNIX_STREAM:
			{
				return "UNIX_STREAM";
			}
		}

		return 'ERROR';
	}
}
