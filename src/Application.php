<?php

namespace DIServer
{

	use DIServer\Container\Container as Container;
	use DIServer\Interfaces\IBootstrapper as IBootstrapper;
	use DIServer\Services\Bootstrapper as Bootstrapper;

	/**
	 * 主程序
	 *
	 * @author Back
	 */
	class Application extends Container
	{
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

		/**
		 * Application的构造函数
		 *
		 * @param string $basePath 应用目录
		 */
		public function __construct($basePath = __DIR__)
		{
			parent::__construct();
			$this->RegisterClassByInstance(get_class($this), $this);
			$this->registerBaseClass();
			$this->registerBaseServiceProviders();
			//	$this->registerCoreContainerAliases();
			if($basePath)
			{
				$this->setBasePath($basePath);
			}
		}

		protected function registerBaseClass()
		{

		}

		protected function registerBaseServiceProviders()
		{
			//	var_dump(interface_exists(IBootstrapper::class));
			$this->RegisterClass(Bootstrapper::class);
			$this->RegisterInterfaceByClass(IBootstrapper::class, Bootstrapper::class);
			$this->RegisterClass(\DIServer\Services\SwooleProxy::class);
		}

		/**
		 * 启动应用程序
		 */
		public function Start()
		{

		}

		public function boot()
		{
			/* @var $bootstrapper \DIServer\Interfaces\IBootstrapper */
			$bootstrapper = $this->GetInstance(IBootstrapper::class);
			$bootstrapper->Boot();
		}

		public function GetBasePath()
		{
			return $this->basePath;
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

		protected function registerCoreContainerAliases()
		{
			$this->SetAlias('App', Application::class);
		}

	}
}
