<?php
namespace DIServer\Bootstraps;

use DIServer\Interfaces\IBootstrap;

/**
 * 启动设定抽象类（默认向启动器注入当前应用）
 * Bootstrap被视作一种特殊的Service
 * 它会在Application正式启动前被调用以初始化一些环境和配置
 * Bootstrap对象是一次性的工具，不会常驻内存
 * 也不会被reload命令重载。
 *
 * @author Back
 */
abstract class Bootstrap implements IBootstrap
{

	public function BeforeBootstrap()
	{

	}

	public function Bootstrap()
	{

	}

	public function AfterBootstrap()
	{
		//echo(get_class($this) . " was booted" . PHP_EOL);
	}
}
