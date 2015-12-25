<?php

namespace DIServer\Bootstraps;

use \DIServer\DI\DIContainer\DIContainer as DIContainer;
use \DIServer\Exceptions\BootException as BootException;

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

	public function Bootstrap()
	{
		parent::Bootstrap();
		$initParams = [
			'serv_host' => '127.0.0.1', 'serv_port' => '13123', 'serv_mode' => SWOOLE_PROCESS,
			'sock_type' => SWOOLE_SOCK_TCP,
		];
		$this->app->GetIOC()->RegisterClass(\swoole_server::class, $initParams);
		$this->swoole = $this->GetIOC()->GetInstance(\swoole_server::class);
		$this->setConfig();
		$this->setProxy();
		$this->swoole->start();
	}

	protected function detectListener()
	{
		$files = AllFile(DI_APP_SERVER_LISTENER_PATH);
	}

	protected function setConfig()
	{
		//加载惯例配置//一次性配置，不用保存在内存中
		$defaultConfig = include DI_CONFIG_PATH . '/Swoole.php';
		//加载自定义配置//一次性配置，不用保存在内存中
		$serverConfig = include DI_APP_SERVER_CONF_PATH . '/Swoole.php';
		//更新配置
		foreach($defaultConfig as $key => $value)
		{
			if(isset($serverConfig[$key]))
			{
				//如果存在Server的重定义，则重定义。
				$defaultConfig[$key] = $serverConfig[$key];
			}
			if(empty($defaultConfig[$key]))
			{
				//如果不存在，unset之
				unset($defaultConfig[$key]);
			}
		}
		$this->settingCheck($defaultConfig);

		$this->swoole->set($defaultConfig);
	}

	protected function settingCheck($setting)
	{
		//有一些配置是DIServer运行必须控制的。
		if($setting['task_ipc_mode'] == 3)
		{
			throw new BootException("Warn: task_ipc_mode设置为争抢模式会导致任务");
		}
		if($setting['dispatch_mode'] != 2)
		{
			throw new BootException("Warn: dispatch_mode设置为争抢模式会导致无序风险");
		}
	}

	protected function setProxy()
	{
		/** @var \DIServer\Services\Service $swooleProxy */
		$swooleProxy = $this->GetIOC()->GetInstance(\DIServer\Services\SwooleProxy::class);
		$swooleProxy->Register();
	}
}
