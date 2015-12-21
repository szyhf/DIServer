<?php

namespace DIServer;

/**
 * 生成所有Handler的工厂
 *
 * @author Back
 */
class HandlersFactory implements IFactory
{
    /**
     * @var \swoole_server $_server 
     */
    private $_server = null;

    public function __construct(\swoole_server &$server)
    {
	$this->_server = $server;
    }

    /**
     * 根据框架逻辑生成所有可用的Handler
     * @param \swoole_server $server 当前进程的$server对象。
     * @return 所有
     */
    public function Create()
    {
	$whiteList = C('HANDLER_WHITE_LIST');
	if (!is_array($whiteList))
	{
	    $whiteList = false;
	}

	$blackList = C('HANDLER_BLACK_LIST');
	if (!is_array($blackList))
	{
	    $blackList = []; //未设置则初始话一个默认的空数组
	}

	//重载Common/Handler
	$handlerFiles = AllFile(DI_APP_COMMON_HANDLER_PATH, true, 'Handler.php');
	$commonHandlers = $this->LoadHandler($handlerFiles, $whiteList, $blackList, '\Common\Handler', $this->_server);

	//重载Server/Handler	
	$handlerFiles = AllFile(DI_APP_SERVER_HANDLER_PATH, true, 'Handler.php');
	$serverHandlers = $this->LoadHandler($handlerFiles, $whiteList, $blackList, "\\" . DI_SERVER_NAME . "\Handler", $this->_server);

	return array_merge(&$commonHandlers, &$serverHandlers);
    }

    function LoadHandler($handlerFile, $whiteList, $blackList, $namespace = '', \swoole_server &$server)
    {
	$handlers = [];
	if (is_array($handlerFile))
	{
	    foreach ($handlerFile as $handlerFile)
	    {
		$tempHandlers = $this->LoadHandler($handlerFile, $whiteList, $blackList, $namespace, $server);
		$handlers = array_merge(&$handlers, &$tempHandlers);
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
		    $handlers = $handler;
		}
	    }
	    catch (\ReflectionException $ex)
	    {
		DILog("ReflectionException On {$handlerFile}:{$ex->getMessage()}");
	    }
	}
	return $handlers;
    }
}
