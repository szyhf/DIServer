<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer;

/**
 * 默认的协议解析器
 *
 * @author Back
 */
class Protocol
{

    public function OnConnect(\swoole_server &$server, $fd, $from_id)
    {
	$info = $server->connection_info($fd, $from_id);
	DILog("fd[{$fd}] Connected From {$info['remote_ip']}:{$info['remote_port']};");
    }

    public function OnClose(\swoole_server &$server, $fd, $from_id)
    {
	$info = $server->connection_info($fd);
	DILog("fd[{$fd}] Closed From {$info['remote_ip']}:{$info['remote_port']}");
    }

    public function OnPacket(\swoole_server &$server, $data, $client_info)
    {
	if ($this->OnPacketCheck($server, $data, $client_info))
	{
	    var_dump($client_info);
	    //$this->HandleOnTask ($server, $data, $fd, $from_id);
	}
    }

    public function OnReceive(\swoole_server &$server, $fd, $from_id, &$data)
    {
	if ($this->OnReceiveCheck($server, $fd, $from_id, $data))
	    $this->HandleOnTask($server, $data, $fd, $from_id);
    }

    public function OnTask(\swoole_server $server, $task_id, $from_id, $param)
    {
	/* @var $handler \DIServer\Handler */
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

    public function OnTaskWorkerError(\swoole_server $server, $task_worker_id, $task_worker_pid, $exit_code)
    {
	DILog(DI_SERVER_NAME . " TaskWorker error on workerID[{$worker_id}]\PID[{$worker_pid}] for code:{$exit_code}");
    }

    public function OnTaskWorkerStart(\swoole_server $server, $task_worker_id)
    {
	$this->ReloadWorkerFunction();
	$this->ReloadWorkerConfig();
	$this->ReloadRequest();
	$this->ReloadHandler($server);
	$this->ReloadService();

	if (!$server->taskworker)//定时器不能运行在TaskWorker上。
	{
	    $this->ReloadTicker();
	    foreach (DITicker() as $ticker)
	    {
		/* @var $ticker \DIServer\Ticker */
		$ticker->TryBind($server, $worker_id);
	    }
	}
	DILog(DI_SERVER_NAME . " TaskWorker Start {$worker_id}" . "/Count<handler>" . count(DIHandler()));
    }

    public function OnTaskWorkerStop(\swoole_server $server, $worker_id)
    {
	DILog(DI_SERVER_NAME . " Worker Stop {$worker_id}" . "/Count<handler>" . count(DIHandler()));
    }

    public function OnWorkerError(\swoole_server $server, $worker_id, $worker_pid, $exit_code)
    {
	DILog(DI_SERVER_NAME . " Worker Error On workerID[{$worker_id}]\pID[{$worker_pid}] for code:{$exit_code}");
    }

    public function OnWorkerStart(\swoole_server $server, $worker_id)
    {
	$this->ReloadWorkerConfig();
	$this->ReloadHandler($server);
    }

    public function OnWorkerStop(\swoole_server $server, $worker_id)
    {
	DILog(DI_SERVER_NAME . " Worker Stop {$worker_id}" . "/Count<handler>" . count(DIHandler()));
    }

    public function OnPipeMessage(\swoole_server $server, $from_worker_id, $message)
    {
	//暂时没用
    }

    public function OnFinish(\swoole_server $server, $task_id, $taskResult)
    {
	//暂时没用
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
//	$cliParams = \DIServer\Package::GetParams($data);
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
     * @param \swoole_server $server 要注入到handler中的当前进程的swoole_server对象
     */
    private function ReloadHandler(\swoole_server &$server)
    {
	$whiteList = C('HANDLER_WHITE_LIST');
	if (!is_array($whiteList))
	    $whiteList = false;

	$blackList = C('HANDLER_BLACK_LIST');
	if (!is_array($blackList))
	{
	    $blackList = []; //未设置则初始话一个默认的空数组
	}

	//重载Common/Handler
	$handlerFiles = AllFile(DI_APP_COMMON_HANDLER_PATH, true, 'Handler.php');
	DILoadHandler($handlerFiles, $whiteList, $blackList, '\Common\Handler', $server);

	//重载Server/Handler	
	$handlerFiles = AllFile(DI_APP_SERVER_HANDLER_PATH, true, 'Handler.php');
	DILoadHandler($handlerFiles, $whiteList, $blackList, "\\" . DI_SERVER_NAME . "\Handler", $server);
    }

    private function ReloadRequest()
    {
	//重载Common/Request
	AutoRequire(DI_APP_COMMON_REQUEST_PATH, true, 'Request.php');
	//重载Server/Request
	AutoRequire(DI_APP_SERVER_REQUEST_PATH, true, 'Request.php');
    }

    private function ReloadTicker()
    {
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
