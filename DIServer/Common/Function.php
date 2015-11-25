<?php

/**
 * 简单的打印一下Log
 * @param type $msg
 * @param type $level
 */
function DILog($msg, $level = "i")
{
    if ($level == 'n')
	return;
    if (is_array($msg))
    {
	foreach ($msg as $key => $value)
	{
	    DILog($key . '=>' . $value, $level);
	}
    }
    else if (is_object($msg))
    {
	echo date("[Y-m-d H:i:s]") . "[{$level}][" . posix_getpid() . ']' . "\n";
	var_dump($msg);
    }
    else
    {
	$msgs = explode("\n", $msg);
	foreach ($msgs as $msg)
	{
	    echo date("[Y-m-d H:i:s]") . "[{$level}][" . posix_getpid() . '] ' . $msg . "\n";
	}
    }
}

/**
 * 快速缓存或读取Handlers
 * @param type $idOrHandlerOrFile 根据传入的数据类型不同，有不同的功能 
 * @return Array 如果传入的是数字，则尝试返回该编号对应的handler集合
 *         TRUE  如果传入的是合法的Handler，则添加入Handler缓存
 *         FALSE 如果传入的是不合法的参数，则返回FALSE
 *         Array 如果不传参数，则返回已HandlerID作为Key的Handler集合
 */
function DIHandler($idOrHandler = NULL)
{
    static $_handlers = [];
    if ($idOrHandler === NULL)//id可能是0，所有要全等于
    {
	//返回所有Handler
	return $_handlers;
    }
    elseif (is_numeric($idOrHandler))
    {
	//需要读取
//	DILog('is_num '.$idOrHandler);
	if (isset($_handlers[$idOrHandler]))
	{
	    //指定HandlerID有对应的Handler
	    $requireHandlerArray = $_handlers[$idOrHandler];
	    return $requireHandlerArray;
	}
	else
	{
	    return [];
	}
    }
    elseif (is_subclass_of($idOrHandler, "DIServer\Handler"))
    {
	//需要添加处理器
	//传入的处理器是合法的处理器	
	/* @var $handler DIServer\Handler */
	$handler = $idOrHandler;
	if (is_numeric($handler->ID()))
	{
	    if (isset($_handlers[$handler->ID()]))
	    {
		$_handlers[$handler->ID()][] = $handler;
	    }
	    else
		$_handlers[$handler->ID()] = [$handler];
	    DILog(get_class($handler) . ' was loaded.', 'n');
	    return TRUE;
	}
    }
    return FALSE;
}

/**
 * 
 * @param type $swoole
 * @param type $request
 * @param type $fd 0表示发送全局, uint表示发给指定fd，array(uint)表示发给数组中所有的fd
 * @return array result 发送失败的fd列表[index=>'$fd']（可能为空）;
 */
function SendRequest(&$swoole, \DIServer\BaseRequest &$request, $fd = 0)
{
    if ($fd === 0)
    {
	$failedList = SendPublicMessage($swoole, $request->ToPackage());
    }
    elseif (is_array($fd))
    {
	$pkg = $request->ToPackage();
	foreach ($fd as $tFd)
	{
	    if (!$swoole->send($tFd, $pkg))
	    {
		$failedList[] = $tFd;
	    }
	}
    }
    else
    {
	if (!$swoole->send($fd, $request->ToPackage()))
	{
	    $failedList[] = $fd;
	}
    }
    return $failedList? : [];
}

/**
 * 
 * @param type $swoole
 * @param \DIServer\BaseRequest $request
 * @return array result 发送失败的fd列表[index=>'$fd'];
 */
function SendPublicRequest(&$swoole, \DIServer\BaseRequest &$request)
{
    return SendPublicMessage($swoole, $request->ToPackage());
}

/**
 * 发送全局消息
 * @param type $swoole
 * @param type $message
 * @return array result 发送失败的fd列表[index=>'$fd'];
 */
function SendPublicMessage($swoole, $message)
{
    foreach ($swoole->connections as $fd)
    {
	if (!$swoole->send($fd, $message))
	{
	    $failedList[] = $tFd;
	}
    }
    return $failedList? : [];
}

/**
 * 向指定端口的所有用户发送消息
 * @param type $swoole
 * @param type $port
 * @param type $msg
 */
function SendPortMessage($swoole, $port, $msg)
{
    $start_fd = 0;
    while (true)
    {
	$conn_list = $swoole->connection_list($start_fd, 99);
	if ($conn_list === false or count($conn_list) === 0)
	{
	    break;
	}
	$start_fd = end($conn_list);

	foreach ($conn_list as $fd)
	{
	    $info = $swoole->connection_info($fd);
	    if ($info['remote_port'] == $port)
	    {
		$swoole->send($fd, $msg);
	    }
	}
    }
}

/**
 * 获取指定路径下的所有文件列表（可递归）
 * @param type $directory 路径
 * @param type $recu 是否递归获取子目录
 * @param type $ext 指定文件名结尾字符串（例如，扩展名）
 * @return array 所有的文件完整路径
 */
function AllFile($directory = __DIR__, $recu = false, $ext = '')
{
    $mydir = dir($directory);
    if (!$mydir)
    {
	DILog("$directory is not exist or available.", 'w');
	return [];
    }
    $files = [];
    $dirs = [];
    if (empty($ext))
    {
	while ($file = $mydir->read())
	{
	    if (( $file == ".") OR ( $file == ".."))
	    {
		continue;
	    }
	    else if ((is_dir("$directory/$file")))
	    {
		if ($recu)//递归，为了确保子目录内的文件在父目录之后才被加载，先缓存子目录路径		
		{
		    $dirs[] = "$directory/$file";
		}
	    }
	    else
	    {
		$files[] = $directory . '/' . $file;
	    }
	}
    }
    else
    {
	while ($file = $mydir->read())
	{
	    if (( $file == ".") OR ( $file == ".."))
	    {
		continue;
	    }
	    else if ((is_dir("$directory/$file")))
	    {
		if ($recu)//递归，为了确保子目录内的文件在父目录之后才被加载，先缓存子目录路径
		{
		    $dirs[] = "$directory/$file";
//		    $files = array_merge(AllFile("$directory/$file", $recu, $ext), $files);
		}
	    }
	    else if (preg_match("/" . $ext . '$/', $file))
	    {
		$files[] = $directory . '/' . $file;
	    }
	}
    }
    $mydir->close();
    natsort($files);
    foreach ($dirs as $dir)
    {
	$files = array_merge($files, AllFile($dir, $recu, $ext));
    }

    return $files;
}

/**
 * 自动加载指定目录下的文件（可递归、可指定文件结尾）
 * @param string $dirPath 指定的路径
 * @param boolean $recu 是否递归
 * @param string $ext 指定文件名结尾
 */
function AutoRequire($dirPath, $recu = false, $ext = '.php')
{
    $files = AllFile($dirPath, $recu, $ext);
    foreach ($files as $file)
    {
//	echo $file."\n";
	require_cache($file);
    }
}

function PHPFatal()
{
    $error = error_get_last();
    if (isset($error['type']))
    {
	switch ($error['type'])
	{
	    case E_ERROR :
	    case E_PARSE :
	    case E_DEPRECATED:
	    case E_CORE_ERROR :
	    case E_COMPILE_ERROR :
		$message = $error['message'];
		$file = $error['file'];
		$line = $error['line'];
		$log = "$message ($file:$line)\nStack trace:\n";
		$trace = debug_backtrace();
		foreach ($trace as $i => $t)
		{
		    if (!isset($t['file']))
		    {
			$t['file'] = 'unknown';
		    }
		    if (!isset($t['line']))
		    {
			$t['line'] = 0;
		    }
		    if (!isset($t['function']))
		    {
			$t['function'] = 'unknown';
		    }
		    $log .= "#$i {$t['file']}({$t['line']}): ";
		    if (isset($t['object']) && is_object($t['object']))
		    {
			$log .= get_class($t['object']) . '->';
		    }
		    $log .= "{$t['function']}()\n";
		}
		if (isset($_SERVER['REQUEST_URI']))
		{
		    $log .= '[QUERY] ' . $_SERVER['REQUEST_URI'];
		}
		$msgs = explode("\n", $log);
		foreach ($msgs as $msg)
		{
		    DILog($msg, 'e');
		}
	}
    }
}

/**
 * 自动加载指定路径的下的配置文件
 * @param type $configPath 路径或路径的集合
 * @return boolean 单个路径时，TRUE表示加载成功了该路径。
 */
function DILoadConfig($configPath)
{
    if (is_array($configPath))
    {
	foreach ($configPath as $path)
	{
	    DILoadConfig($path);
	}
    }
    else if (file_exists($configPath))
    {
	C(load_config($configPath));
	return TRUE;
    }
    else
    {
	return FALSE;
    }
}

/**
 * 在Task进程调用执行指定HandlerID的Handler
 * @param type $handlerID Handler的ID
 * @param \swoole_server $server 服务对象
 * @param array $handlerParams 传给Handler的额外参数
 * @param intger $taskID 指定执行任务的HandlerID，会覆盖Handler的默认ID
 */
function DICallHandler($handlerID, \swoole_server &$server, array $handlerParams = [], $taskID = 0)
{
    $handlers = DIHandler($handlerID);
    foreach ($handlers as $handlerKey => $handler)
    {
	/* @var \DIServer\Handler $handler */
	if (NULL !== $handler)
	{
	    $handlerParams['handlerID'] = $handlerID;
	    $handlerParams['handlersKey'] = $handlerKey;
	    $server->task($handlerParams, $taskID? : $handler->TaskID($handlerParams));
	}
    }
}

/**
 * 加载Handler文件
 * @param string $handlerFile Handler的文件完整路径
 * @param array $whiteList Handler的白名单
 * @param array $blackList Handler的黑名单
 * @param string $namespace Handler的命名空间
 * @param \swoole_server $server 要注入到Handler中的当前进程的swoole_server对象
 * @return boolean 加载是否成功
 */
function DILoadHandler($handlerFile, $whiteList, $blackList, $namespace = '', \swoole_server &$server)
{
    if (is_array($handlerFile))
    {
	foreach ($handlerFile as $handlerFile)
	{
	    DILoadHandler($handlerFile, $whiteList, $blackList, $namespace, $server);
	}
    }
    else if (file_exists($handlerFile))
    {
	$fileName = array_pop(explode('/', $handlerFile));
	$sortClassName = rtrim($fileName, '.php');
	$className = $namespace . '\\' . $sortClassName;
	//黑白名单判定逻辑，黑名单优先。
	if ($whiteList)
	{
	    if (array_search($sortClassName, $whiteList) === false)
	    {
		return;
	    }
	}
	if (array_search($sortClassName, $blackList) !== false)//找的到的时候，因为返回的是Key的值，Key可能是0，所以要用!==做判断
	{
	    return;
	}
	require_cache($handlerFile);
	$DIHandlerClass = new \ReflectionClass("\DIServer\Handler");
	try
	{

	    $handlerClass = new \ReflectionClass($className);
	    if ($handlerClass->isSubclassOf($DIHandlerClass))
	    {
		$handler = $handlerClass->newInstanceArgs(['server' => $server]);
		DIHandler($handler);
	    }
	}
	catch (\ReflectionException $ex)
	{
	    DILog("ReflectionException On {$handlerFile}:{$ex->getMessage()}");
	}
    }
    else
    {
	return FALSE;
    }
}

/**
 * 将IP列表转化成[long=>long,long=>long,...]的类型
 * @param type $IPList 允许使用以下方式定义，即单个IP，IP段，IP区间（区间内不应重合）
 *                     ['192.168.0.1','192.168.0.1/16','192.168.0.1'=>'192.168.255.255']
 * @return type [long=>long,long=>long,...]的类型的结果
 */
function LongIPListPrepared($IPList)
{
    $longIPList = [];
    foreach ($IPList as $key => $value)
    {
	$longKeyIP = ip2long($key);
	if ($longKeyIP)
	{
	    //区间型
	    $longValIP = ip2long($value);
	    $baseIP = ip2long($ip);
	    $longIPList[$longKeyIP] = $longValIP;
	}
	elseif (is_numeric($key))
	{
	    //单个'192.168.0.1'
	    $longValIP = ip2long($value);
	    if ($longValIP)
	    {
		$longIPList[$longValIP] = $longValIP;
	    }
	    else
	    {
		//区段型'192.168.0.1/16'
		$baseIPAndAera = explode('/', $value, 2);
		$baseIP = $baseIPAndAera[0];
		$baseAera = $baseIPAndAera[1];
		$startLongIP = ip2long($baseIP) + 1;
		$endLongIP = ip2long($baseIP) + pow(2, $baseAera) - 1;
		$longIPList[$startLongIP] = $endLongIP;
	    }
	}
    }
    return $longIPList;
}

/**
 * 基于配置文件的快速IP检测，用于支持黑名单和白名单方法。
 * 使用了static缓存，随着进程重启而重新加载。
 * @param type $ip 要监测的IP地址'xxx.xxx.xxx.xxx’
 * @param type $ipListConfKey	配置文件中保存了IP名单的配置的键名
 * 				IP名单允许使用以下方式定义，即单个IP，IP段，IP区间（区间内不应重合）
 * 				['192.168.0.1','192.168.0.1/16','192.168.0.1'=>'192.168.255.255']
 * @return boolean TRUE表示名单中存在，FALSE表示名单中不存在。
 */
function DI_IPCheck($ip, $ipListConfKey)
{
    $longIp = ip2long($ip);
    static $longIpList = [];
    $longIpToCheck = $longIpList[$ipListConfKey];
    if (!$longIpToCheck)
    {
	$ipList = C($ipListConfKey); //从配置中读取
	$longIpToCheck = LongIPListPrepared($ipList);
	$longIpList[$ipListConfKey] = $longIpToCheck;
    }
    //存在缓存
    foreach ($longIpToCheck as $key => $value)
    {
	if ($key <= $longIp && $longIp <= $value)
	    return true;
    }
    return false;
}

/**
 * 缓存Ticker的方法
 * @staticvar array $_tickers
 * @param type $ticker 新的Ticker，如果为null则返回所有Ticker的数组
 * @return boolean|array 所有Ticker的数组或者添加是否成功
 */
function DITicker($ticker = null)
{
    static $_tickers = [];
    if ($ticker === null)
    {
	return $_tickers;
    }
    elseif (is_subclass_of($ticker, "DIServer\Ticker"))
    {
	$_tickers[] = $ticker;
	return TRUE;
    }
    return FALSE;
}

/**
 * 从指定路径加载Ticker
 * @param type $tickerFile Ticker的路径
 * @param type $namespace Ticker的命名空间
 * @return boolean Ticker是否加载成功
 */
function DILoadTicker($tickerFile, $namespace = '')
{
    if (is_array($tickerFile))
    {
	foreach ($tickerFile as $path)
	{
	    DILoadTicker($path, $namespace);
	}
    }
    else if (file_exists($tickerFile))
    {
	$fileName = array_pop(explode('/', $tickerFile));
	$sortClassName = rtrim($fileName, '.php');
	$className = $namespace . '\\' . $sortClassName;

	require_cache($tickerFile);
	$TickerClass = new \ReflectionClass("\DIServer\Ticker");

	try
	{
	    $tickerClass = new \ReflectionClass($className);
	    if ($tickerClass->isSubclassOf($TickerClass))
	    {
		$ticker = $tickerClass->newInstance();
		DITicker($ticker);
	    }
	}
	catch (\ReflectionException $ex)
	{
	    DILog("ReflectionException On {$tickerFile}:{$ex->getMessage()}");
	}
	return TRUE;
    }
    else
    {
	return FALSE;
    }
}
