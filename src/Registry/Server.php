<?php
//这里提供服务的基本映射
return [
	\DIServer\Interfaces\Swoole\ISwooleProxy::class   => \DIServer\Swoole\SwooleProxy::class,
	\DIServer\Interfaces\Swoole\IMasterServer::class  => \DIServer\Swoole\MasterServer::class,
	\DIServer\Interfaces\Swoole\IManagerServer::class => \DIServer\Swoole\ManagerServer::class,
	\DIServer\Interfaces\Swoole\IWorkerServer::class  => \DIServer\Swoole\WorkerServer::class,
	\DIServer\Interfaces\Swoole\ITaskServer::class    => \DIServer\Swoole\TaskServer::class
];