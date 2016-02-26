<?php

namespace DIServer\Bootstraps;

use DIServer\Services\Application;

class InitMonitor extends Bootstrap
{
	public function Bootstrap()
	{
		Application::RegisterClass(\DIServer\Monitor\SwooleTable::class);
		Application::RegisterInterfaceByClass(\DIServer\Interfaces\IMonitor::class, \DIServer\Monitor\SwooleTable::class);
		$monitor = Application::GetInstance(\DIServer\Interfaces\IMonitor::class);
		$monitor->Bind();
	}
}