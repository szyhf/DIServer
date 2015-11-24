<?php

namespace DIServer;

/**
 * 回调重载帮助类实例
 *
 * @author Back
 */
class BaseReloadHelper
{

    public function OnConnect(\swoole_server &$server, &$fd, &$from_id)
    {
	$info = $server->connection_info($fd, $from_id);
	DILog("fd[{$fd}] Connected From {$info['remote_ip']}:{$info['remote_port']};");
    }

    public function OnClose(&$server, &$fd, &$from_id)
    {
	$info = $server->connection_info($fd);
	DILog("fd[{$fd}] Closed From {$info['remote_ip']}:{$info['remote_port']}");
    }

    public function OnFinish(\swoole_server &$server, &$task_id, &$taskResult)
    {
	
    }

    public function OnPipeMessage(\swoole_server &$server, &$from_worker_id, &$message)
    {
	DILog("pipe from {$from_worker_id}: {$message}");
    }

    public function OnReceive(\swoole_server &$server, &$fd, &$from_id, &$data)
    {
	if ($this->OnReceiveCheck($server, $fd, $from_id, $data))
	    $this->HandleOnTask($server, $data, $fd, $from_id);
    }

    /**
     * 回调接受UDP数据，Swoole1.7.18及以上支持。
     * @param \swoole_server $server swoole_server对象
     * @param type $data 收到的数据内容，可能是文本或者二进制内容
     * @param array $client_info 客户端信息包括address/port/server_socket3项数据
     */
    public function onPacket(\swoole_server $server, $data, $client_info)
    {
	if ($this->OnPacketCheck($server, $data, $client_info))
	{
	    var_dump($client_info);
	    //$this->HandleOnTask ($server, $data, $fd, $from_id);
	}
    }

    public function OnTask(\swoole_server &$server, &$task_id, &$from_id, &$param)
    {
	/* @var $handler \DIServer\DIHandler */
	$handler = DIHandler($param['handlerID'])[$param['handlersKey']];
	if (!$handler)
	{
	    DILog("Calling On not exist HandlerID " . $param['handlerID'] . ".");
	    return;
	}
	$info = $server->connection_info($data['fd'], $from_id);
	$param['taskID'] = $task_id;
	$param['fromID'] = $from_id;
	$param['server'] = &$server;
	G('HandlerStart');
	$handler->__BeforeRun($param);
	$handler->Run($param);
	$handler->__AfterRun($param);
	G('HandlerEnd');
	$timeUsed = G('HandlerStart', 'HandlerEnd');
	if ($timeUsed >= C('HANDLER_SLOW_CHECK'))
	{
	    $handler->SlowLog($param); //允许用户自定义慢处理参数日志
	    DILog(get_class($handler) . " use " . $timeUsed . "s on task {$task_id}", 'w');
	}
    }

    public function OnWorkerError(\swoole_server &$serv, &$worker_id, &$worker_pid, &$exit_code)
    {
	DILog(DI_SERVER_NAME . " Worker Error On workerID[{$worker_id}]\pID[{$worker_pid}] for code:{$exit_code}");
    }

    public function OnWorkerStart(\swoole_server &$server, &$worker_id)
    {
	$this->ReloadWorkerFunction();
	$this->ReloadWorkerConfig();
	$this->ReloadRequest();
	$this->ReloadHandler();
	$this->ReloadService();

	if (!$server->taskworker)//定时器不能运行在TaskWorker上。
	{
	    $this->ReloadTicker();
	    foreach (DITicker() as $ticker)
	    {
		/* @var $ticker \DIServer\BaseTicker */
		$ticker->TryBind($server, $worker_id);
	    }
	}
	DILog(DI_SERVER_NAME . " Worker Start {$worker_id}" . "/Count<handler>" . count(DIHandler()));
    }

    public function OnWorkerStop(\swoole_server &$server, &$worker_id)
    {
	DILog(DI_SERVER_NAME . " Worker Stop {$worker_id}" . "/Count<handler>" . count(DIHandler()));
    }

    /**
     * 根据包命令将工作交给不同的Handler，如果有多个Handler可以响应，则不同的Handler可能在不同的Task中响应
     * @param \swoole_server $server Swoole_Server对象
     * @param type $data 接收到的完整的数据包
     * @param type $fd 连接的标识
     * @param type $task_id 
     * @param type $from_id
     */
    function HandleOnTask(\swoole_server &$server, &$data, $fd, $from_id)
    {

	//确定服务端应该用什么Handler来解析
	$cliHandlerID = \DIServer\Package::GetHandlerID($data);
	$cliParams = \DIServer\Package::GetParams($data);
//	DILog("{$cliHandlerID}\\{$cliParams}");
//	DILog("Count<handler>".count($this->handlerArray));
	if ($cliHandlerID !== NULL)
	{
	    $handlerParams = [
		'fd' => $fd, //客户端的唯一标识，用于回发信息
		'params' => $data//客户端的完整报文
	    ]; //传入标准的解析参数
	    DICallHandler($cliHandlerID, $server, $handlerParams);
	}
    }

    /**
     * 允许用户针对收到的TCP包进行合法性检查，仅投递合法的包给Task做进一步处理
     * @param \swoole_server $server 当前的Server
     * @param type $fd 当前包的来源fd
     * @param type $from_id 
     * @param type $data 当前包的内容
     * @return boolean 返回TRUE表示可以提交给Task处理，返回FALSE表示当前包不合法直接丢弃。
     */
    protected function OnReceiveCheck(\swoole_server &$server, &$fd, &$from_id, &$data)
    {
	if (strlen($data) < 8)
	    return FALSE; //不合法包，跳过
	else
	    return TRUE;
    }

    /**
     * 允许用户针对收到的UDP包进行合法性检查，仅投递合法的包给Task做进一步处理
     * @param \swoole_server $server 当前的Server
     * @param type $fd 当前包的来源fd
     * @param type $from_id 
     * @param type $data 当前包的内容
     * @return boolean 返回TRUE表示可以提交给Task处理，返回FALSE表示当前包不合法直接丢弃。
     */
    protected function OnPacketCheck(\swoole_server &$server, $data, $client_info)
    {
	if (strlen($data) < 8)
	    return FALSE; //不合法包，跳过
	else
	    return TRUE;
    }

    /**
     * 重新加载Worker方法。
     */
    private function ReloadWorkerFunction()
    {
	//加载Worker的方法库
	AutoRequire(DI_APP_SERVER_WORKER_COMMON_PATH, true, 'Function.php');
    }

    /**
     * 重新加载WorkerConfig
     */
    private function ReloadWorkerConfig()
    {
	$configPath = AllFile(DI_APP_SERVER_WORKER_CONFIG_PATH, true, 'Config.php');
	DILoadConfig($configPath);
    }

    /**
     * 重新加载Handler
     */
    private function ReloadHandler()
    {
	$whiteList = C('HANDLER_WHITE_LIST');
	if (!is_array($whiteList))
	    $whiteList = false;

	$blackList = C('HANDLER_BLACK_LIST');
	if (!is_array($blackList))
	{
	    $blackList = []; //未设置则初始话一个默认的空数组
	}

	//重载DIServer/Handler
	$handlerFiles = AllFile(DI_HANDLER_PATH, true, 'Handler.php');
	DILoadHandler($handlerFiles, $whiteList, $blackList, '\DIServer\Handler');

	//重载Common/Handler
	$handlerFiles = AllFile(DI_APP_COMMON_HANDLER_PATH, true, 'Handler.php');
	DILoadHandler($handlerFiles, $whiteList, $blackList, '\Common\Handler');

	//重载Server/Handler	
	$handlerFiles = AllFile(DI_APP_SERVER_HANDLER_PATH, true, 'Handler.php');
	DILoadHandler($handlerFiles, $whiteList, $blackList, "\\" . DI_SERVER_NAME . "\Handler");
    }

    private function ReloadRequest()
    {
	//重载DIServer/Request
	AutoRequire(DI_REQUEST_PATH, true, 'Request.php');
	//重载Common/Request
	AutoRequire(DI_APP_COMMON_REQUEST_PATH, true, 'Request.php');
	//重载Server/Request
	AutoRequire(DI_APP_SERVER_REQUEST_PATH, true, 'Request.php');
    }

    private function ReloadTicker()
    {
	//重载框架级的Ticker
	$tickerFiles = AllFile(DI_TICKER_PATH, true, 'Ticker.php');
	DILoadTicker($tickerFiles, "\DIServer\Ticker");
	//重载Common级的Ticker
	$tickerFiles = AllFile(DI_APP_COMMON_TICKER_PATH, true, 'Ticker.php');
	DILoadTicker($tickerFiles, "\Common\Ticker");
	//重载ServerWorker级的Ticker
	$tickerFiles = AllFile(DI_APP_SERVER_TICKER_PATH, TRUE, 'Ticker.php');
	DILoadTicker($tickerFiles, "\\" . DI_SERVER_NAME . "\Ticker");
    }

    private function ReloadService()
    {
	//重载ServerWorker级的Service
	AutoRequire(DI_APP_SERVER_SERVICE_PATH, TRUE, 'Service.php');
    }

}
