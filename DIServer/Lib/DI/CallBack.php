<?php

namespace DIServer;

/**
 * SwooleServer的回调类
 *
 * @author Back
 */
class CallBack
{

    /**
     * @var BaseReloadHelper  
     */
    private $ReloadHelper = null;

    public function OnStart(\swoole_server $server)
    {
	//不能热重启
	DILog(DI_SERVER_NAME . " Start",'n');
    }

    public function OnShutdown(\swoole_server $server)
    {
	//不能热重启
	DILog(DI_SERVER_NAME . " Shutdown",'n');
    }

    public function OnWorkerStart(\swoole_server $server, $worker_id)
    {
	$this->InitWorkerReloadHelper();
	$this->ReloadHelper->OnWorkerStart($server, $worker_id);
    }

    public function OnWorkerError(\swoole_server $serv, $worker_id, $worker_pid, $exit_code)
    {
	//无法热重载
	DILog("Error On Worker[{$worker_id}].PID[{$worker_pid}, exit({$exit_code})]", 'e');
    }

    public function OnWorkerStop(\swoole_server $server, $worker_id)
    {
	$this->ReloadHelper->OnWorkerStop($server, $worker_id);
    }

    public function OnConnect(\swoole_server $server, $fd, $from_id)
    {
	$this->ReloadHelper->OnConnect($server, $fd, $from_id);
    }

    public function OnClose($server, $fd, $from_id)
    {
	$this->ReloadHelper->OnClose($server, $fd, $from_id);
    }

    public function OnReceive(\swoole_server $server, $fd, $from_id, $data)
    {
	$this->ReloadHelper->OnReceive($server, $fd, $from_id, $data);
    }

    public function OnTask(\swoole_server $server, $task_id, $from_id, $param)
    {
	$this->ReloadHelper->OnTask($server, $task_id, $from_id, $param);
    }

    public function OnFinish(\swoole_server $server, $task_id, $taskResult)
    {
	$this->ReloadHelper->OnFinish($server, $task_id, $taskResult);
    }

    public function OnPipeMessage(\swoole_server $server, $from_worker_id, $message)
    {
	$this->ReloadHelper->OnPipeMessage($server, $from_worker_id, $message);
    }
    
    public function OnPacket(\swoole_server &$server, $data, $client_info)
    {
	$this->ReloadHelper->onPacket($server, $data, $client_info);
    }

    /**
     * 重新加载WorkerReloadHelper
     */
    protected function InitWorkerReloadHelper()
    {
	//初始化基类
	$baseReloadHelperClass = new \ReflectionClass("DIServer\ReloadHelper");
	$reloadHelperFilePath = DI_APP_SERVER_WORKER_PATH . '/ReloadHelper.php';
	if (file_exists($reloadHelperFilePath))
	{
	    require_cache($reloadHelperFilePath);
	    try
	    {
		$className = DI_SERVER_NAME . '\\Worker\\ReloadHelper';
		$reloadHelperClass = new \ReflectionClass($className);
		//生成
		if ($reloadHelperClass->isSubclassOf($baseReloadHelperClass))
		{
		    $this->ReloadHelper = $reloadHelperClass->newInstance();
		    return;
		}
	    }
	    catch (\ReflectionException $ex)
	    {
//		DILog("ReflectionException On {$reloadHelperFile}:{$ex->getMessage()}",'n');
	    }
	}
	DILog("Can't Create {$reloadHelperFilePath}, Use default BaseReloadHelper.",'n');
	$this->ReloadHelper = $baseReloadHelperClass->newInstance();
    }

}
