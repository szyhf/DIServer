<?php

namespace DIServer\Bootstraps;

use DIServer\DI\DIContainer\DIContainer as DIContainer;

/**
 * 初始化swooler_server，设置监听
 *
 * @author Back
 */
class InitSwooleServer extends Bootstrap
{

    public function Bootstrap()
    {
	parent::Bootstrap();
	$initParams = [
	    'serv_host' => '127.0.0.1',
	    'serv_port' => '13123',
	    'serv_mode' => SWOOLE_PROCESS,
	    'sock_type' => SWOOLE_SOCK_TCP
	];
	$this->app->IOC()->RegisterClass(\swoole_server::class, $initParams);
    }

    protected function detectListener()
    {
	$files = AllFile(DI_APP_SERVER_LISTENER_PATH);
	
    }

}
