<?php
namespace DIServer\Bootstraps;

use DIServer\Application as Application;
use DIServer\Services\Service as Service;

/**
 * 启动设定抽象类（默认向启动器注入当前应用）
 *
 * @author Back
 */
abstract class Bootstrap extends Service
{

	public function BeforeBootstrap()
	{

	}

	public function Bootstrap()
	{

	}

	public function AfterBootstrap()
	{
		var_dump(get_class($this) . " was booted");
	}
}
