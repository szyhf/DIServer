<?php
//在Master进程和Manager进程的代理
return [
	\DIServer\Interfaces\Swoole\ISwooleProxy::class   => \DIServer\Swoole\SwooleProxy::class,
	\DIServer\Interfaces\Swoole\IMasterServer::class  => \DIServer\Swoole\MasterServer::class,
	\DIServer\Interfaces\Swoole\IManagerServer::class => \DIServer\Swoole\ManagerServer::class
];