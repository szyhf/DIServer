<?php

namespace DIServer\DI;

/**
 * 进程管理
 * 参考自acabin/laravoole
 * @author Back
 */
class ProcessManager implements IProcessManager
{

    protected $masterPID = NULL;
    protected $pidFilePath;

    /**
     * 
     */
    public function __construct()
    {
	if ($server)
	    $this->masterPID = $server->master_pid;
	else
	    $this->masterPID = $this->GetMasterPID();
	$this->pidFilePath = DI_LOG_PATH . '/' . DI_SERVER_NAME . '.pid';
    }

    public function OnStart(\swoole_server $server)
    {
	if ($this->GetMasterPID())
	{
	    die(DI_SERVER_NAME . ' has already running.' . PHP_EOL);
	}
    }

    public function Restart()
    {
	$pid = sendSignal(SIGTERM);
	$time = 0;
	while (posix_getpgid($pid) && $time <= 10)
	{
	    usleep(100000);
	    $time++;
	}
	if ($time > 100)
	{
	    die('Stop ' . DI_SERVER_NAME . ' timeout.' . PHP_EOL);
	}
	$this->Start();
    }

    public function Stop()
    {
	$this->sendSignal(SIGTERM);
    }

    public function Reload()
    {
	$this->sendSignal(SIGUSR1);
    }

    public function Status()
    {
	
    }

    /**
     * 获取当前服务的主进程PID
     */
    public function GetMasterPID()
    {
	if ($this->masterPID)
	    return $this->masterPID;
	else
	    return $this->GetPIDFromFile();
    }

    public function GetPIDFromFile()
    {

	if (file_exists($this->pidFilePath))
	{
	    $pid = file_get_contents($this->pidFilePath);
	    if (posix_getpgid($pid))
	    {
		return $pid;
	    }
	    else
	    {
		unlink($this->pidFilePath);
	    }
	}
    }

    protected function sendSignal($sig)
    {
	if ($pid = $this->GetMasterPID())
	{
	    posix_kill($pid, $sig);
	}
	else
	{
	    die(DI_SERVER_NAME . ' is not running.' . PHP_EOL);
	}
    }

}
