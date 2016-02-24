<?php

namespace DIServer
{

	use DIServer\Container\Container as Container;
	use DIServer\Helpers\Ary;
	use DIServer\Helpers\IO;
	use DIServer\Interfaces\IApplication;
	use \DIServer\Interfaces\IBootstrapper;
	use DIServer\Services\Bootstrapper;
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
					if($this->HasRegistered($iface))
					{
						//使用新的服务实现替代旧的服务实现（在后续作用域生效）
						$this->Unregister($iface);
					}
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
			Bootstrapper::Boot();
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
					//echo "命令格式：[Path to PHP]php {ServerName} {Param}". PHP_EOL;
					echo "可选参数如下（不区分大小写）：" . PHP_EOL;
					echo "start:   以守护进程的方式启动服务。" . PHP_EOL;
					echo "test:    以交互进程的方式启动服务。" . PHP_EOL;
					echo "stop:    柔性停止当前服务的守护进程（可能需要用户权限，仅完成正在进行的Worker\Task后退出）。" . PHP_EOL;
					echo "kill:    强制停止当前服务的守护进程（可能需要用户权限，适用于服务严重阻塞导致stop无效的情况）。" . PHP_EOL;
					echo "reload:  热重载Worker/Task进程（可能需要用户权限）。" . PHP_EOL;
					echo "restart: 柔性重启当前服务（可能需要用户权限，5s超时）。" . PHP_EOL;
					echo "status:  查看当前服务的运行状态（可能需要用户权限）。" . PHP_EOL;
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
				posix_kill($pid, SIGTERM);
				echo "Try kill -15(SIGTERM) to process $pid." . PHP_EOL;
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
				exec($cmd, $output);
				if(array_search($pid, $output) !== false)
				{
					foreach($output as $pid)
					{
						echo "Try kill -9(SIGKILL) to process $pid." . PHP_EOL;
						posix_kill($pid, SIGKILL);
					}
				}
				else
				{
					echo "Can't find server's pid." . PHP_EOL;
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
				exec("ps -x|grep $pid", $output);//检查pid是否真的存在
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

