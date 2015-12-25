<?php

namespace DIServer\Bootstraps;

/**
 * 设置swoole_server的配置
 *
 * @author Back
 */
class SetSwooleConfig extends Bootstrap
{

    /**
     * @var \swoole_server  
     */
    protected $server;

    public function __construct(\swoole_server $server)
    {
	$this->server = $server;
    }

    public function Bootstrap()
    {
	parent::Bootstrap();
	//加载惯例配置//一次性配置，不用保存在内存中	
	$defaultConfig = include DI_CONFIG_PATH . '/Swoole.php';
	//加载自定义配置//一次性配置，不用保存在内存中	
	$serverConfig = include DI_APP_SERVER_CONF_PATH . '/Swoole.php';
	//更新配置
	foreach ($defaultConfig as $key => $value)
	{
	    if (isset($serverConfig[$key]))
	    {
		//如果存在Server的重定义，则重定义。
		$defaultConfig[$key] = $serverConfig[$key];
	    }
	    if (empty($defaultConfig[$key]))
		unset($defaultConfig[$key]);
	}
	$this->server->set($defaultConfig);
    }

    protected function settingCheck($setting)
    {
	//有一些配置是DIServer运行必须控制的。
	if ($setting['task_ipc_mode'] == 3)
	{
	    ; //warn:task_ipc_mode设置为争抢模式会导致任务
	}
	if ($setting['dispatch_mode'] != 2)
	{
	    ; //warn:无序风险
	}
    }

}
