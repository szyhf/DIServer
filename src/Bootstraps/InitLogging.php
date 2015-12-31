<?php
namespace DIServer\Bootstraps;

use DIServer\Interfaces\Log\ILog;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

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
		//$driverConf = include $this->getApp()->GetFrameworkPath() . '/Config/Log.php';
		//if(isset($driverConf['Driver']))
		//{
		//	$class = $driverConf['Driver'];
		//	if(class_exists($class))
		//	{
		//		$logPath = $this->getApp()->GetBasePath() . '/TestLog/test.log';
		//		$this->getApp()->RegisterClassByFactory($class, function () use ($class, $logPath)
		//		{
		//			$monoLog = new Logger(DI_SERVER_NAME);
		//
		//			$monoLog->pushHandler(new StreamHandler($logPath));
		//
		//			return $monoLog;
		//		});
		//		$this->getApp()->RegisterInterfaceByClass(ILog::class, $class);
		//		///** @var ILog $monoLog */
		//		//$monoLog = $this->getApp()->GetInstance(ILog::class);
		//		//$monoLog->Alert('hello log.');
		//	}
		//	else
		//	{
		//		throw new BootException("Log driver {$class} is not exist.");
		//	}
		//}
		//else
		//{
		//	throw new BootException("Config/Log.php didn't configure 'Driver'.");
		//}
	}

	public function registerLogger()
	{

	}
}
