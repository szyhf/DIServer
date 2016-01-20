<?php

namespace DIServer
{

	use DIServer\Container\Container as Container;
	use DIServer\Interfaces\IApplication;
	use \DIServer\Interfaces\IBootstrapper;

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
		 * 公共目录
		 *
		 * @var string
		 */
		protected $commonPath;

		/**
		 * @var string
		 */
		protected $frameworkPath;

		/**
		 * 服务目录
		 *
		 * @var string
		 */
		protected $serverPath;

		/**
		 * Application的构造函数
		 *
		 * @param string $basePath 应用目录
		 */
		public function __construct($basePath)
		{
			parent::__construct();
			static::SetInstance($this);
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
			$this->RegisterClassByInstance(__CLASS__, $this);
			$this->RegisterInterfaceByClass(IApplication::class, get_class($this));
		}

		protected function bindBaseService()
		{
			$baseServices = include $this->GetFrameworkPath() . '/Registry/Application.php';
			$this->bindService($baseServices);
		}

		/**
		 * 根据数组[$iface=>$class]快速绑定服务的快捷方法
		 * 若未提供$iface则会仅注册$class
		 *
		 * @param array $iface2service
		 *
		 * @throws \DIServer\Container\NotExistException
		 * @throws \DIServer\Container\RegistedException
		 */
		protected function bindService(array $iface2service)
		{
			foreach($iface2service as $iface => $serv)
			{
				if(class_exists($serv))
				{
					$this->RegisterClass($serv);
					if($this->IsAbstract($iface))
					{
						$this->RegisterInterfaceByClass($iface, $serv);
					}
				}
				else
				{
					echo "Bind service [$iface]=>[$serv] is not exist.\n";
				}
			}
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
			/* @var $bootstrapper \DIServer\Interfaces\IBootstrapper */
			$bootstrapper = $this->__get(IBootstrapper::class);
			$bootstrapper->Boot();
		}

		/**
		 * @return string
		 */
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
			$this->serverPath = $this->GetBasePath() . '/app/' . $this->GetServerName();
			$this->commonPath = $this->GetBasePath() . '/app/Common';

			return $this;
		}

		/**
		 * 获取服务名称
		 *
		 * @return string
		 */
		public function GetServerName()
		{
			return DI_SERVER_NAME;
		}

		/**
		 * 应用程序目录
		 *
		 * @return string
		 */
		public function GetServerPath()
		{
			return $this->serverPath;
		}

		public function GetCommonPath()
		{
			return $this->commonPath;
		}

		public function  __get($name)
		{
			//快速访问已经注册的单例
			if($this->IsRegistered($name))
			{
				return $this->GetInstance($name);
			}
			else
			{
				return null;
			}
		}
	}
}
