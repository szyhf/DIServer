<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer;

/**
 * 在OnWorkerStart和OnTaskWorkerStart中实现热重载业务代码
 *
 * @author Back
 */
abstract class ReloadProtocol
{
    /**
     * 热重启实现核心，分发数据包的工作放在Worker中
     * @param \swoole_server $server
     * @param int $worker_id
     */
    function OnWorkerStart(\swoole_server &$server, &$worker_id)
    {
	//暂时没有特别理想的解决方案。。不想另外弄个注册树。。先这样好了。
	//也没多多少内存。。
	$this->ReloadWorkerConfig();
	$this->ReloadHandler($server);
    }
    
    /**
     * 热重启实现核心，所有业务工作都放在Task中
     * @param \swoole_server $server
     * @param int $worker_id
     */
    public function OnTaskWorkerStart(\swoole_server &$server, &$worker_id)
    {
	$this->ReloadWorkerFunction();
	$this->ReloadWorkerConfig();
	$this->ReloadRequest();
	$this->ReloadHandler($server);
	$this->ReloadService();

	$this->ReloadTicker();
	foreach (DITicker() as $ticker)
	{
	    /* @var $ticker \DIServer\Ticker */
	    $ticker->TryBind($server, $worker_id);
	}
	DILog(DI_SERVER_NAME . " TaskWorker Start {$worker_id}" . "/Count<handler>" . count(DIHandler()),'n');
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
