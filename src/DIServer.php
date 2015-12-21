<?php

namespace DIServer;

if (php_sapi_name() !== 'cli')
    die('Should run in CLI mode.');

// 检测PHP环境
if (version_compare(PHP_VERSION, '5.5.0', '<'))
    die('require PHP > 5.5.0 !');
// 检测Swoole环境
if (version_compare(\swoole_version(), '1.7.17', '<'))
    die('Require swoole > 1.7.17');

\defined('DI_DAEMONIZE') or \define('DI_DAEMONIZE', 0);
\defined('DI_CHECK_SERVER_DIR') or define('DI_CHECK_SERVER_DIR', 1);
//框架级
\defined('DI_DISERVER_PATH') or \define('DI_DISERVER_PATH', __DIR__);
\defined('DI_COMMON_PATH') or \define('DI_COMMON_PATH', DI_DISERVER_PATH . '/Common');
\defined('DI_CONFIG_PATH') or \define('DI_CONFIG_PATH', DI_DISERVER_PATH . '/Conf');
\defined('DI_LIB_PATH') or \define('DI_LIB_PATH', DI_DISERVER_PATH . '/Lib');
\defined('DI_REQUEST_PATH') or \define('DI_REQUEST_PATH', DI_LIB_PATH . '/Request');
\defined('DI_HANDLER_PATH') or \define('DI_HANDLER_PATH', DI_LIB_PATH . '/Handler');
\defined('DI_TICKER_PATH') or \define('DI_TICKER_PATH', DI_LIB_PATH . '/Ticker');
//APP级
\defined('DI_APP_PATH') or \define('DI_APP_PATH', realpath(APP_PATH));
\defined('DI_APP_COMMON_PATH') or \define('DI_APP_COMMON_PATH', DI_APP_PATH . '/Common');
\defined('DI_APP_COMMON_HANDLER_PATH') or \define('DI_APP_COMMON_HANDLER_PATH', DI_APP_COMMON_PATH . '/Handler');
\defined('DI_APP_COMMON_REQUEST_PATH') or \define('DI_APP_COMMON_REQUEST_PATH', DI_APP_COMMON_PATH . '/Request');
\defined('DI_APP_COMMON_TICKER_PATH') or \define('DI_APP_COMMON_TICKER_PATH', DI_APP_COMMON_PATH . '/Ticker');
//Server级
\defined('DI_SERVER_NAME') or die('DI_SERVER_NAME should set');
\defined('DI_APP_SERVER_PATH') or \define('DI_APP_SERVER_PATH', DI_APP_PATH . '/' . DI_SERVER_NAME);
\defined('DI_APP_SERVER_COMMON_PATH') or \define('DI_APP_SERVER_COMMON_PATH', DI_APP_SERVER_PATH . '/Common');
\defined('DI_APP_SERVER_CONF_PATH') or \define('DI_APP_SERVER_CONF_PATH', DI_APP_SERVER_PATH . '/Conf');
\defined('DI_APP_SERVER_HANDLER_PATH') or \define('DI_APP_SERVER_HANDLER_PATH', DI_APP_SERVER_PATH . '/Handler');
\defined('DI_APP_SERVER_REQUEST_PATH') or \define('DI_APP_SERVER_REQUEST_PATH', DI_APP_SERVER_PATH . '/Request');
\defined('DI_APP_SERVER_SERVICE_PATH') or \define('DI_APP_SERVER_SERVICE_PATH', DI_APP_SERVER_PATH . '/Service');
\defined('DI_APP_SERVER_TICKER_PATH') or \define('DI_APP_SERVER_TICKER_PATH', DI_APP_SERVER_PATH . '/Ticker');
\defined('DI_APP_SERVER_TEMP_PATH') or \define('DI_APP_SERVER_TEMP_PATH', DI_APP_SERVER_PATH . '/Temp');
//Worker级
\defined('DI_APP_SERVER_WORKER_PATH') or \define('DI_APP_SERVER_WORKER_PATH', DI_APP_SERVER_PATH . '/Worker');
\defined('DI_APP_SERVER_WORKER_COMMON_PATH') or \define('DI_APP_SERVER_WORKER_COMMON_PATH', DI_APP_SERVER_WORKER_PATH . '/Common');
\defined('DI_APP_SERVER_WORKER_CONFIG_PATH') or \define('DI_APP_SERVER_WORKER_CONFIG_PATH', DI_APP_SERVER_WORKER_PATH . '/Conf');
//Server配置
\defined('DI_LOG_PATH') or \define('DI_LOG_PATH', DI_APP_SERVER_PATH . '/Log');
\defined('DI_LOG_FILE_NAME') or \define('DI_LOG_FILE_NAME', DI_SERVER_NAME . '.log');

class DIServer
{

    /**
     * 使用的服务
     * @var type \swoole_server
     */
    protected $server = null;
    protected $callBack = null;

    public function __construct(bool $debug = null)
    {
	//控制器初始化	
	if (method_exists($this, '_initialize'))
	    $this->_initialize();
    }

    protected function _initialize()
    {
	die('_initialize');
	//加载应用方法
	//惯例方法提供核心支持，在对象外加载
	$this->LoadDIFunction(); //惯例方法
	register_shutdown_function('PHPFatal'); //处理php自身致命错误
	$this->LoadDIConfig(); //惯例配置
	$this->LoadDILibrary(); //加载DIServer工具库
//	$this->LoadCommonFunction();//公共方法（已经在ThinkPHP中被加载）
//	$this->LoadCommonConfig();//公共配置（已经在ThinkPHP中被加载）

	if (DI_CHECK_SERVER_DIR)
	{
	    \DIServer\Build::checkDir(DI_SERVER_NAME);
	}

	$this->LoadServerFunction(); //应用方法
	$this->LoadServerConfig(); //应用配置
	//初始化应用服务，设置监听
	$this->InitServerListener();

	//初始化服务的设置
	$this->InitServerSettings();

	//设置回调
	$this->InitServerCallBack();

	//设置回调方法
	$this->SetOnServer();

	//启动服务
	$this->Start();
    }

    /**
     * 加载惯例方法
     */
    protected function LoadDIFunction()
    {
	require_once (DI_COMMON_PATH . '/Function.php');
    }

    /**
     * 初始化惯例配置
     */
    protected function LoadDIConfig()
    {
	$conventionPath = DI_CONFIG_PATH . "/Convention.php"; //惯例配置文件
	DILoadConfig($conventionPath);
	if (APP_DEBUG)
	{
	    $debugPath = DI_CONFIG_PATH . "/Debug.php"; //调试配置文件
	    DILoadConfig($debugPath);
	}
    }

    protected function LoadDILibrary()
    {
	AutoRequire(DI_LIB_PATH, true);
    }

    /**
     * 加载应用方法（当前Server独占）
     */
    protected function LoadServerFunction()
    {
	AutoRequire(DI_APP_SERVER_COMMON_PATH, true, 'Function.php');
    }

    /**
     * 初始化应用配置（当前Server独占）
     */
    protected function LoadServerConfig()
    {
	//加载Server目录下所有的配置文件（以Config.php结尾）
	$configsPath = AllFile(DI_APP_SERVER_CONF_PATH, true, 'Config.php');
	DILoadConfig($configsPath);
    }

    /**
     * 初始化公共配置（与ThinkPHP共用）
     */
    protected function LoadCommonConfig()
    {
	//因为与ThinkPHP公用，已经被加载。
    }

    /**
     * 初始化监听配置
     */
    protected function InitServerListener()
    {
	$DI_LISTENERS = C('DI_LISTENERS');
	if (is_array($DI_LISTENERS))
	{
	    foreach ($DI_LISTENERS as $listener)
	    {
		if ($this->server === null)
		{
		    //初始化服务
		    $this->server = new \swoole_server($listener['Host'], $listener['Port'], SWOOLE_PROCESS, $listener['Type']);
		}
		else
		{
		    //添加被监听的端口
		    $this->server->addlistener($listener['Host'], $listener['Port'], $listener['Type']);
		}
		DILog("A listener is set on {$listener['Host']}:{$listener['Port']},for type {$listener['Type']}.",'n');
	    }
	}
	else
	{
	    DILog("DI_LISTENERS尚未配置。",'e');
	}
    }

    /**
     * 初始化服务配置
     */
    protected function InitServerSettings()
    {
	//读取设置名称映射表
	$DISettingMap = C('SW_DI_SETTING_MAP');
	foreach ($DISettingMap as $key => $value)
	{
	    $set = C($value);
	    if (empty($set))
	    {
		//如果配置为空，则去掉当前配置，让swoole使用默认值
		unset($DISettingMap[$key]);
	    }
	    else
	    {
		//如果配置存在，则替换默认配置。
		$DISettingMap[$key] = $set;
	    }
	}

	if (!is_array($DISettingMap))
	{
	    DILog('$DISettingMap init failed.','e');
	    exit();
	}

	//守护进程化的配置特例处理
	$DISettingMap['daemonize'] = DI_DAEMONIZE;

	$this->server->set($DISettingMap);
    }

    protected function InitServerCallBack()
    {
	//初始化基类
	$baseCallBackClass = new \ReflectionClass("DIServer\CallBack");
	$callBackFilePath = DI_APP_SERVER_WORKER_PATH . '/CallBack.php';
	if (file_exists($callBackFilePath))
	{
	    require_cache($callBackFilePath);
	    try
	    {
		$className = DI_SERVER_NAME . '\\Worker\\CallBack';		
		$callBackClass = new \ReflectionClass($className);
		//生成
		if ($callBackClass->isSubclassOf($baseCallBackClass))
		{
		    $this->callBack = $callBackClass->newInstance();
		    return;
		}
	    }
	    catch (\ReflectionException $ex)
	    {
		DILog("ReflectionException On {$callBackFile}:{$ex->getMessage()}");
	    }
	}
	DILog("Can't Create {$callBackFilePath}, Use default BaseCallBack.",'n');
	$this->callBack = $baseCallBackClass->newInstance();
    }

    /**
     * 设置回调
     */
    protected function SetOnServer()
    {
	if ($this->server)
	{
	    $callBack = &$this->callBack;
	    $this->server->on("start", [$callBack, 'OnStart']);
	    $this->server->on("connect", [$callBack, 'OnConnect']);
	    $this->server->on("receive", [$callBack, 'OnReceive']);
	    $this->server->on("close", [$callBack, 'OnClose']);
	    $this->server->on("task", [$callBack, 'OnTask']);
	    $this->server->on('finish', [$callBack, 'OnFinish']);
	    $this->server->on('shutdown', [$callBack, 'OnShutdown']);
	    $this->server->on('WorkerStart', [$callBack, 'OnWorkerStart']);
	    $this->server->on('WorkerStop', [$callBack, 'OnWorkerStop']);
	    $this->server->on('WorkerError', [$callBack, 'OnWorkerError']);
	    $this->server->on('PipeMessage', [$callBack, 'OnPipeMessage']);
	    DILog('Swoole_version is ' . swoole_version(),'n');
	    if (version_compare(\swoole_version(), '1.7.17'))
	    {
		//OnPacket方法与现有的基于Receive的业务逻辑有差异，暂时不支持。
//		DILog('OnPacket is activated.');
//		$this->server->on('Packet', [$callBack, 'OnPacket']); //1.7.18特性
	    }
	}
    }

    protected function Start()
    {
	$this->server->start();
    }

}

$server = new DIServer();
