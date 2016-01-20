<?php
namespace DIServer\Bootstraps;

use DIServer\Interfaces\ILog;
use DIServer\Services\Bootstrap;
use DIServer\Services\Log;

/**
 * 初始化日志工具
 *
 * @author Back
 */
class InitLogging extends Bootstrap
{
	/**
	 * @throws \DIServer\Bootstraps\BootException
	 */
	public function Bootstrap()
	{
		$this->getApp()
		     ->RegisterClass(\DIServer\Log\DILog::class);
		$this->getApp()
		     ->RegisterInterfaceByClass(ILog::class, \DIServer\Log\DILog::class);
		//$this->getApp()
		//     ->RegisterInterfaceByClass(\DIServer\Services\Log::class, \DIServer\Log\DILog::class);
	}

	public function registerLogger()
	{

	}
}
