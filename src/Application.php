<?php

namespace DIServer
{

	use DIServer\Container\Container as Container;
	use DIServer\Interfaces\Bootstraps\IBootstrapper as IBootstrapper;
	use DIServer\Interfaces\IApplication;

	/**
	 * 主程序
	 *
	 * @author Back
	 */
	class Application extends Container implements IApplication
	{
		/**
		 * 项目目录
		 *
		 * @var string
		 */
		protected $basePath;

		/**
		 * @var string
		 */
		protected $frameworkPath;

		/**
		 * Application的构造函数
		 *
		 * @param string $basePath 应用目录
		 */
		public function __construct($basePath)
		{
			if($basePath)
			{
				$this->setBasePath($basePath);
			}
			$this->setFrameworkPath(__DIR__);
			$this->bindBaseClass();
			$this->bindBaseService();
			$this->bindCoreAliases();
		}

		protected function bindBaseClass()
		{
			$this->RegisterInterfaceByClass(IApplication::class, get_class($this));
			var_dump($this);
			die;
		}

		protected function bindBaseService()
		{
			//echo ($this->GetFrameworkPath() . '/Registry/Base.php') . "\n";
			$baseServices = include $this->GetFrameworkPath() . '/Registry/Base.php';
			foreach($baseServices as $iface => $serv)
			{
				if(class_exists($serv))
				{
					$this->RegisterClass($serv);
					if($this->isAbstract($iface))
					{
						$this->RegisterInterfaceByClass($iface, $serv);
					}
				}
			}
			//$this->RegisterClass(Bootstrapper::class);
			//$this->RegisterInterfaceByClass(IBootstrapper::class, Bootstrapper::class);
			//$this->RegisterClass(\DIServer\Services\SwooleProxy::class);
		}

		/**
		 *  注册一些核心服务的别名，便于调用
		 */
		protected function bindCoreAliases()
		{
			$alias = [
				'App'    => get_class($this),
				'Swoole' => \swoole_server::class
			];
			foreach($alias as $alia => $type)
			{
				$this->SetAlias($alia, $type);
			}

			return $this;
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
		 * 设置应用目录
		 *
		 * @param  string $basePath
		 *
		 * @return $this
		 */
		public function SetBasePath($basePath)
		{
			$this->basePath = realpath(rtrim($basePath, '\/'));
			return $this;
		}

		/**
		 * 获取框架基础路径
		 *
		 * @return string
		 */
		public function GetFrameworkPath()
		{
			return $this->frameworkPath;
		}

		/**
		 * 设置框架基础路径
		 *
		 * @param string $frameworkPath
		 *
		 * @return Application
		 */
		protected function setFrameworkPath($frameworkPath = __DIR__)
		{
			$this->frameworkPath = realpath(rtrim($frameworkPath, '\/'));

			return $this;
		}


	}
}
