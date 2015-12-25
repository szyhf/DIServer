<?php
namespace DIServer\Bootstraps;

use \DIServer\Application as Application;

/**
 * 启动设定抽象类（默认向启动器注入当前应用）
 *
 * @author Back
 */
abstract class Bootstrap
{
	/**
	 * @var \DIServer\Application
	 */
	protected $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * @return \DIServer\Application
	 */
	protected function GetApp()
	{
		return $this->app;
	}

	/**
	 * @return \DIServer\DI\DIContainer
	 */
	protected function GetIOC()
	{
		return $this->GetApp()->GetIOC();
	}

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
