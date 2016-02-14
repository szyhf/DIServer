<?php

namespace DIServer\Bootstraps;

use DIServer\Services\Application;
use DIServer\Services\Bootstrap;
use DIServer\Interfaces\Swoole\ISwooleProxy;

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
		$initParams = [
			'serv_host' => '0.0.0.0',
			'serv_port' => '13123',
			'serv_mode' => SWOOLE_PROCESS,
			'sock_type' => SWOOLE_SOCK_TCP,
		];
		Application::RegisterClass(\swoole_server::class, $initParams);
		$this->swoole = Application::GetInstance(\swoole_server::class);
		$this->setConfig();
		$this->initProxy();//构造时已经自动完成了回调注册
		$this->initMonitor();
		echo $this->formatSettings($this->swoole);
		$this->swoole->start();

	}

	protected function setConfig()
	{
		//加载惯例配置//一次性配置，不用保存在内存中
		$defaultConfig = include Application::GetFrameworkPath() . '/Config/Swoole.php';
		//加载自定义配置//一次性配置，不用保存在内存中
		$serverConfig = include Application::GetServerPath() . '/Config/Swoole.php';
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
		if($setting['task_worker_num'] <= 0)
		{
			throw new BootException("Error: 配置swoole.task_worker_num被设置为0。");
		}
		if($setting['task_ipc_mode'] != 2)
		{
			throw new BootException("Error: 配置swoole.task_ipc_mode设置不是2。");
		}
		if($setting['dispatch_mode'] == 1 || $setting['dispatch_mode'] == 3)
		{
			throw new BootException("Error: 配置swoole.dispatch_mode=1/3时，底层会屏蔽onConnect/onClose事件，原因是这2种模式下无法保证onConnect/onClose/onReceive的顺序。");
		}
		if(isset($setting['chroot']))
		{
			throw new BootException("Error: 配置swoole.chroot会导致autoloader无法在工作\任务进程正常使用，请确定你能处理（如修改autoloader的路径）然后过来注释这个异常。");
		}
	}

	protected function initProxy()
	{
		/** @var \DIServer\Interfaces\Swoole\ISwooleProxy $swooleProxy */
		Application::GetInstance(ISwooleProxy::class);
	}

	protected function detectListener()
	{
		$files = AllFile(DI_APP_SERVER_LISTENER_PATH);
	}

	protected function initMonitor()
	{
		Application::RegisterClass(\DIServer\Monitor\SwooleTable::class);
		Application::RegisterInterfaceByClass(\DIServer\Interfaces\IMonitor::class, \DIServer\Monitor\SwooleTable::class);
		$monitor = Application::GetInstance(\DIServer\Interfaces\IMonitor::class);
		$monitor->Bind();
	}

	private function formatSettings(\swoole_server $server)
	{
		$longestKey = 0;
		foreach($server->setting as $key => $set)
		{
			if(strlen($key) > $longestKey)
			{
				$longestKey = strlen($key);
			}
		}
		$settings = "";//"=============================================================" . PHP_EOL;
		$settings .= 'Swoole Settings:' . PHP_EOL;
		foreach($server->setting as $key => $set)
		{
			$settings .= str_pad($key, $longestKey + 1, ' ', STR_PAD_RIGHT) . '=> ' . $set . PHP_EOL;
		}
		$settings .= "=============================================================" . PHP_EOL;

		return $settings;
	}
}
