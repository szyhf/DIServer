<?php

namespace DIServer;

use \DIServer\DI\DIContainer as Container;
use \DIServer\Interfaces\IBootstrapper as IBootstrapper;
use \DIServer\Services\Bootstrapper as Bootstrapper;

/**
 * 主程序
 *
 * @author Back
 */
class Application
{

	/**
	 * 默认容器
	 *
	 * @var \DIServer\DI\DIContainer
	 */
	protected $ioc;

	/**
	 * 项目目录
	 *
	 * @var string
	 */
	protected $basePath;

	/**
	 * 环境目录
	 *
	 * @var string
	 */
	protected $envermentPath;

	public function __construct(Container $container, $basePath = __DIR__)
	{
		$this->ioc = $container;
		$this->registerBaseClass();
		$this->registerBaseServiceProviders();
		//	$this->registerCoreContainerAliases();
		if($basePath)
		{
			$this->setBasePath($basePath);
		}
	}

	/**
	 * 获取当前容器
	 *
	 * @return \DIServer\DI\DIContainer
	 */
	public function GetIOC()
	{
		return $this->ioc;
	}

	protected function registerBaseClass()
	{

	}

	protected function registerBaseServiceProviders()
	{
		//	var_dump(interface_exists(IBootstrapper::class));
		$this->GetIOC()->RegisterClass(Bootstrapper::class);
		$this->GetIOC()->RegisterInterfaceByClass(IBootstrapper::class, Bootstrapper::class);
		$this->GetIOC()->RegisterClass(\DIServer\Services\SwooleProxy::class);
	}

	protected function registerCoreContainerAliases()
	{

	}

	public function boot()
	{
		/* @var $bootstrapper \DIServer\Interfaces\IBootstrapper */
		$bootstrapper = $this->GetIOC()->GetInstance(IBootstrapper::class);
		$bootstrapper->Boot();
	}

	/**
	 * Set the base path for the application.
	 *
	 * @param  string $basePath
	 *
	 * @return $this
	 */
	public function SetBasePath($basePath)
	{
		$this->basePath = realpath(rtrim($basePath, '\/'));

		//        $this->bindPathsInContainer();

		return $this;
	}

	public function GetBasePath()
	{
		return $this->basePath;
	}

}
