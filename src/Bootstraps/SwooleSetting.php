<?php

namespace DIServer\Bootstraps;


use DIServer\Services\Application;
use DIServer\Services\Container;
use DIServer\Services\Log;
use DIServer\Services\Server;
use DIServer\Services\Config;

class SwooleSetting extends Bootstrap
{

	public function Bootstrap()
	{
		$this->setConfig();
		//	Log::Info("=============================================================");
		Log::Info(['Swoole Settings' => Server::GetSetting()]);
		//	Log::Info("=============================================================");
		//echo $this->formatSettings();
	}

	protected function setConfig()
	{
		$swooleConfig = Config::Get('Swoole');
		foreach($swooleConfig as $key => $item)
		{
			if($swooleConfig[$key] === '')
			{
				unset($swooleConfig[$key]);
			}
		}
		$this->settingCheck($swooleConfig);

		Server::Set($swooleConfig);
		Config::Delete('Swoole');
	}

	protected function settingCheck(&$setting)
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
			throw new BootException("Error: 配置swoole.chroot会导致autoloader无法在工作\\任务进程正常使用，请确定你能处理（如修改autoloader的路径）然后过来注释这个异常。");
		}
		if(!isset($setting['package_eof']))
		{
			$setting['package_eof'] = '';//疑似swoole bug，如果不显式指定package_eof则无法正常收包。
		}
		if(!isset($setting['task_tmpdir']))
		{
			$setting['task_tmpdir'] = Application::GetServerPath('/Runtimes/TaskTemp');
		}
		if(!isset($setting['log_file']))
		{
			$setting['log_file'] = Application::GetServerPath('/Runtimes/Log/' . Application::GetServerName() . '.log');
		}
		if(!isset($setting['message_queue_key']))
		{
			$setting['message_queue_key'] = ftok(Application::GetServerPath(), 0);
		}
		$setting['daemonize'] = DI_DAEMONIZE;
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
			if($set !== '' && $set !== null)
			{
				if(is_bool($set))
				{
					$set = $set ? 'TRUE' : 'FALSE';
				}
				$settings .= str_pad($key, $longestKey + 1, ' ', STR_PAD_RIGHT) . '=> ' . $set . PHP_EOL;
			}
		}
		$settings .= "=============================================================" . PHP_EOL;

		return $settings;
	}
}