<?php

namespace DIServer
{

	use DIServer\Container\Container as Container;
	use DIServer\Helpers\Ary;
	use DIServer\Helpers\IO;
	use DIServer\Interfaces\IApplication;
	use \DIServer\Interfaces\IBootstrapper;
	use DIServer\Services\Event;

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
		public function __construct($basePath, $args = [])
		{
			//根据文件名定义服务名
			\define('DI_SERVER_NAME', current(explode('.php', $args[0])));
			parent::__construct();
			$this->setFrameworkPath(__DIR__);
			if($basePath)
			{
				$this->setBasePath($basePath);
			}

			return $this->handleArgs($args);
		}

		protected function handleArgs($args)
		{
			$commond = isset($args[1]) ? $args[1] : "help";
			switch(strtolower($commond))//兼容一下大小写
			{
				case 'start':
				{
					$this->handleStart();
					break;
				}
				case 'test':
				{
					$this->handleTest();
					break;
				}
				case 'stop':
				{
					$this->handleStop();
					break;
				}
				case 'restart':
				{
					$this->handleRestart();
					break;
				}
				case 'reload':
				{
					$this->handleReload();
					break;
				}
				case 'status':
				{
					$this->handleStatus();
					break;
				}
				case 'help':
				default:
				{
					echo "可选参数如下：" . PHP_EOL;
					echo "start:   以守护进程的方式启动服务。" . PHP_EOL;
					echo "test:    以交互进程的方式启动服务。" . PHP_EOL;
					echo "stop:    柔性停止当前服务的守护进程（可能需要用户权限，仅完成正在进行的Worker\Task后退出）。" . PHP_EOL;
					echo "reload:  热重载Worker/Task进程（可能需要用户权限）。" . PHP_EOL;
					echo "restart: 柔性重启当前服务（可能需要用户权限，5s超时）。" . PHP_EOL;
					echo "status:  查看当前服务的运行状态（可能需要用户权限）。" . PHP_EOL;
				}
			}
		}

		protected function bindBaseClass()
		{
			$this->RegisterClassByInstance(__CLASS__, $this);
			$this->RegisterInterfaceByClass(IApplication::class, get_class($this));
		}

		/**
		 * 自动注册（快捷工具）
		 * 会自动搜索FrameworkPath、CommonPath、ServerPath的Registry目录并自动继承实现
		 *
		 * @param            $registry
		 * @param bool|false $build
		 *
		 * @return array
		 * @throws \DIServer\Container\NotExistException
		 * @throws \DIServer\Container\NotRegistedException
		 * @throws \DIServer\Container\RegistedException
		 */
		public function AutoRegistry($registryFile, $build = false)
		{
			$files= $this->GetConventionPaths("/Registry/$registryFile");
			$registry = [];
			foreach($files as $registryFilePath)
			{
				if(file_exists($registryFilePath))
				{
					$newRegistry = include $registryFilePath;
					if(is_array($newRegistry))
					{
						Ary::MergeRecursive($registry, $newRegistry);
					}
				}
			}

			$instances = [];
			foreach($registry as $iface => $serv)
			{
				if(class_exists($serv))
				{
					if(!$this->HasRegistered($serv))
					{
						$this->RegisterClass($serv);
						if($this->IsAbstract($iface))
						{
							$this->RegisterInterfaceByClass($iface, $serv);
						}
						if($build)
						{
							$instances[] = $this->GetInstance($serv);
						}
					}
				}
				else
				{
					echo "AutoRegistry [$iface]=>[$serv] is not exist.\n";
				}
			}

			return $instances;
		}

		/**
		 * 获取框架基础路径
		 *
		 * @return string
		 */
		public function GetFrameworkPath($addPath = '')
		{
			return $this->frameworkPath . $addPath;
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
			//$alias = [
			//	'App'    => get_class($this),
			//	'Swoole' => \swoole_server::class
			//];
			//foreach($alias as $alia => $type)
			//{
			//	$this->SetAlias($alia, $type);
			//}

			return $this;
		}

		/**
		 * 启动应用程序
		 */
		public function Start()
		{
			static::SetInstance($this);
			$this->bindBaseClass();
			$this->AutoRegistry("Application.php");
			$this->bindCoreAliases();
			Event::Add('OnMasterStart', [$this, 'RecordPID']);
			/* @var $bootstrapper \DIServer\Interfaces\IBootstrapper */
			$bootstrapper = $this->__get(IBootstrapper::class);
			$bootstrapper->Boot();
		}

		/**
		 * @return string
		 */
		public function GetBasePath($addPath = '')
		{
			return $this->basePath . $addPath;
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
			$this->serverPath = $this->GetBasePath('/app/' . $this->GetServerName());
			$this->commonPath = $this->GetBasePath('/app/Common');

			return $this;
		}

		/**
		 * 获得惯例配置路径组（按顺序为FrameworkPath、CommonPath、ServerPath）
		 * @param $addPath
		 *
		 * @return array
		 */
		public function GetConventionPaths($addPath)
		{
			return [
				$this->GetFrameworkPath($addPath),
				$this->GetCommonPath($addPath),
				$this->GetServerPath($addPath)
			];
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
		public function GetServerPath($addPath = '')
		{
			return $this->serverPath . $addPath;
		}

		public function GetCommonPath($addPath = '')
		{
			return $this->commonPath . $addPath;
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

		protected function handleTest()
		{
			\define('DI_DAEMONIZE', 0);;
			if($pid = $this->readPID())
			{
				echo DI_SERVER_NAME . " has already run, master pid = $pid." . PHP_EOL;
			}
			else
			{
				$this->recordPID();
				$this->Start();
			}
		}

		protected function handleStart()
		{
			\define('DI_DAEMONIZE', 1);;
			if($pid = $this->readPID())
			{
				echo DI_SERVER_NAME . " has already run, master pid = $pid." . PHP_EOL;
			}
			else
			{
				$this->recordPID();
				$this->Start();
			}
		}

		protected function handleReload()
		{
			if($pid = $this->readPID())
			{
				posix_kill($pid, SIGUSR1);
				echo "Try kill -10 to $pid." . PHP_EOL;
			}
			else
			{
				echo DI_SERVER_NAME . " is not running." . PHP_EOL;
			}
		}

		protected function handleStop()
		{
			if($pid = $this->readPID())
			{
				//exec("kill -15 $pid");
				posix_kill($pid, SIGTERM);
				echo "Try kill -15(SIGTERM) to $pid." . PHP_EOL;
			}
		}

		protected function handleRestart()
		{
			//尝试5次，每次等待1s，避免程序锁死
			for($i = 0; $i < 5; $i++)
			{
				$this->handleStop();
				sleep(1);
				if(!$pid = $this->readPID())
				{
					$this->handleTest();

					return;
				}
			}
		}

		protected function handleStatus()
		{
			if($pid = $this->readPID())
			{
				exec("pstree -ap|grep " . DI_SERVER_NAME . ".php", $output);
				foreach($output as $out)
				{
					if(strpos($out, 'status') !== false)
					{
						break;
					}
					if(strpos($out, 'grep') !== false)
					{
						break;
					}
					echo $out . PHP_EOL;
				}
			}
		}

		protected function readPID()
		{
			$processPath = $this->GetServerPath("/Runtimes/Process/MasterPID");
			$pid = false;
			if(file_exists($processPath))
			{
				$pid = file_get_contents($processPath);
				exec("ps -x|grep $pid", $output);//检查pid是否真的存在
				$mastProc = current($output);
				$pid = strpos($mastProc, trim($pid)) === 0 ? $pid : false;//pid存在
				$pid = strpos($mastProc, DI_SERVER_NAME . ".php") !== false ? $pid : false;//进程名是否正确
			}

			return $pid;
		}

		public function RecordPID()
		{
			$pid = posix_getpid();
			$processPath = $this->GetServerPath("/Runtimes/Process/MasterPID");
			file_put_contents($processPath, $pid, LOCK_EX);
		}
	}
}

