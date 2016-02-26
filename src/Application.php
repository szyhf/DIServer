<?php

namespace DIServer
{

	use DIServer\Container\Container as Container;
	use DIServer\Helpers\Ary;
	use DIServer\Interfaces\IApplication;
	use DIServer\Services\Bootstrapper;
	use DIServer\Services\Event;
	use DIServer\Services\Log;

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
		 * 框架目录
		 *
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
		 * @param array  $args     传入参数
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
			$files = $this->GetConventionPaths("/Registry/$registryFile");
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
					}
					if($this->IsAbstract($iface))
					{
						if($this->HasRegistered($iface))
						{
							//使用新的服务实现替代旧的服务实现（在后续作用域生效）
							$this->Unregister($iface);
						}
						$this->RegisterInterfaceByClass($iface, $serv);
					}
					if($build)
					{
						$instances[$iface] = $this->GetInstance($serv);
					}
				}
				else
				{
					echo "AutoRegistry [$iface]=>[$serv] is not exist.\n";
				}
			}

			return $instances;
		}

		public function AutoBuildCollection($registryFile, $iface = '')
		{
			$files = $this->GetConventionPaths("/Registry/$registryFile");

			if($this->isAbstract($iface))
			{
				$check = function (string $class) use ($iface)
				{
					if(class_exists($class))
					{
						$refClass = new \ReflectionClass($class);

						if($refClass->isSubclassOf($iface))
						{
							return true;
						}
						else
						{
							Log::Warning("Try to auto-build $class, but class isn't instance of $iface.");
						}
					}
					else
					{
						Log::Warning("Try to auto-build $class, but class not exist.");
					}

					return false;
				};
			}
			else
			{
				$check = function (string $class)
				{
					if(class_exists($class))
					{
						return true;
					}
					else
					{
						Log::Warning("Try to auto-build $class, but class not exist.");
					}//没有传入iface就不检查默认true
					return false;
				};
			}

			$newClasses = [];
			foreach($files as $file)
			{
				if(file_exists($file))
				{
					$tempClass = include $file;
					Ary::MergeRecursive($newClasses, $tempClass);
				}
			}

			$newInstances = [];
			foreach($newClasses as $key => $newClass)
			{
				if(is_array($newClass))
				{
					foreach($newClass as $tempClass)
					{
						if($check($newClass))
						{
							$newInstances[$key][] = $this->BuildWithClass($newClass);
						}
					}
				}
				else
				{
					if($check($newClass))
					{
						$newInstances[$key] = $this->BuildWithClass($newClass);
					}
				}

			}

			return $newInstances;
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
			Bootstrapper::Boot();

		}

		/**
		 * 获取当前目录（入口目录）
		 *
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
			$this->serverPath = $this->GetBasePath('/App/' . $this->GetServerName());
			$this->commonPath = $this->GetBasePath('/App/Common');

			return $this;
		}

		/**
		 * 获得惯例配置路径组（按顺序为FrameworkPath、CommonPath、ServerPath）
		 *
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

		/**
		 * 获取公共目录
		 *
		 * @param string $addPath
		 *
		 * @return string
		 */
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
				case 'kill':
				{
					$this->handleKill();
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
					$colorPrefix = "\033[0m";
					$colorSuffix = "\033[0m";
					echo "Usage: [path to php]php <file> {command}" . PHP_EOL;
					echo "Support command (ignore case):" . PHP_EOL;
					echo "\t{$colorPrefix}start{$colorSuffix}   :Run as daemon." . PHP_EOL;
					echo "\t{$colorPrefix}test{$colorSuffix}    :Run as shell." . PHP_EOL;
					echo "\t{$colorPrefix}stop{$colorSuffix}    :Flexible stop the daemon process of current server." . PHP_EOL;
					echo "\t{$colorPrefix}kill{$colorSuffix}    :Force stop the daemon process of current server." . PHP_EOL;
					echo "\t{$colorPrefix}reload{$colorSuffix}  :Reload worker/task worker process." . PHP_EOL;
					echo "\t{$colorPrefix}restart{$colorSuffix} :Flexible restart current server." . PHP_EOL;
					echo "\t{$colorPrefix}status{$colorSuffix}  :Show the server status." . PHP_EOL;
					echo "\t{$colorPrefix}build{$colorSuffix}   :Auto build base directory and files of " . DI_SERVER_NAME . PHP_EOL;
				}
			}
		}

		protected function handleTest()
		{
			\define('DI_DAEMONIZE', 0);;
			if($this->getPIDLock())
			{
				$this->Start();
			}
			else
			{
				$pid = $this->readPID();
				echo DI_SERVER_NAME . " has already running, master pid = $pid." . PHP_EOL;
			}
		}

		protected function handleStart()
		{
			\define('DI_DAEMONIZE', 1);;
			if($this->getPIDLock())
			{
				$this->Start();
			}
			else
			{
				$pid = $this->readPID();
				echo DI_SERVER_NAME . " has already running, master pid = $pid." . PHP_EOL;
			}
		}

		protected function handleReload()
		{
			if($this->getPIDLock())
			{
				echo DI_SERVER_NAME . " is not running." . PHP_EOL;
			}
			else
			{
				$pid = $this->readPID();
				posix_kill($pid, SIGUSR1);
				echo "Try kill -10(SIGUSR1) to process $pid." . PHP_EOL;
			}
		}

		protected function handleStop()
		{
			if($this->getPIDLock())
			{
				echo DI_SERVER_NAME . " is not running." . PHP_EOL;
			}
			else
			{
				$pid = $this->readPID();
				echo "Try kill -15(SIGTERM) to process $pid." . PHP_EOL;
				posix_kill($pid, SIGTERM);
			}
		}

		protected function handleKill()
		{
			if($this->getPIDLock())
			{
				echo DI_SERVER_NAME . " is not running." . PHP_EOL;
			}
			else
			{
				$pid = $this->readPID();
				$cmd = "ps -x|egrep \"" . DI_SERVER_NAME . ".php\"|awk '{print $1}'" . PHP_EOL;
				exec($cmd, $output);//检查pid是否真的存在
				if(array_search($pid, $output) !== false)
				{
					foreach($output as $pid)
					{
						echo "Try kill -9(SIGKILL) to process $pid." . PHP_EOL;
						posix_kill($pid, SIGKILL);
					}

					return $pid;
				}
			}
		}

		protected function handleRestart()
		{
			//尝试5次，每次等待1s，避免程序锁死
			for($i = 0; $i < 5; $i++)
			{
				if($this->getPIDLock())
				{
					$this->handleStart();

					return;
				}
				$this->handleStop();
				sleep(1);
			}
		}

		protected function handleStatus()
		{
			if($this->getPIDLock())
			{
				echo DI_SERVER_NAME . " is not running." . PHP_EOL;
			}
			else
			{
				$pid = $this->readPID();
				echo DI_SERVER_NAME . " is running, master pid = $pid." . PHP_EOL;
			}
		}

		protected function readPID()
		{
			$processPath = $this->GetServerPath("/Runtimes/Process/MasterPID");
			$pid = false;
			if(file_exists($processPath))
			{
				$pid = file_get_contents($processPath);
				$cmd = "ps -x|egrep \"" . DI_SERVER_NAME . ".php\"|awk '{print $1}'" . PHP_EOL;
				exec($cmd, $output);//检查pid是否真的存在
				if(array_search($pid, $output) !== false)
				{
					return $pid;
				}
				else
				{
					return false;
				}
				$mastProc = current($output);
				$pid = strpos($mastProc, trim($pid)) === 0 ? $pid : false;//pid存在
				//$pid = strpos($mastProc, DI_SERVER_NAME . ".php") !== false ? $pid : false;//进程名是否正确
			}

			return $pid;
		}

		public function RecordPID()
		{
			/** @var \swoole_server $server */
			$server = $this->GetInstance(\swoole_server::class);
			//通过swoole内置方法获取Master Pid，防止方法在其它进程被误用导致记录pid出错
			if($pid = $server->master_pid)
			{
				$pidLock = $this->getPIDLock();
				ftruncate($pidLock, 0);      // truncate file​
				fwrite($pidLock, $pid);//写入当前进程pid（应在Master进程中调用）
				fflush($pidLock);
			}
		}

		protected function getPIDLock()
		{
			if($this->pidLock)
			{
				return $this->pidLock;
			}
			else
			{
				$pid_file = $this->GetServerPath("/Runtimes/Process/MasterPID");
				$fp = fopen($pid_file, 'r+');
				if(flock($fp, LOCK_EX | LOCK_NB))
				{
					$this->pidLock = $fp;

					return $this->pidLock;
				}
				else
				{
					return false;
				}
			}
		}
	}
}

