<?php
//这里提供服务的基本映射
return [
	\DIServer\DI\Interfaces\ISwooleProxy::class => \DIServer\Services\SwooleProxy::class,
	//swoole服务注册
	\DIServer\Interfaces\IMasterServer::class   => \DIServer\Swoole\MasterServer::class,
	\DIServer\Interfaces\IManagerServer::class  => \DIServer\Swoole\ManagerServer::class,
	\DIServer\Interfaces\IWorkerServer::class   => \DIServer\Swoole\WorkerServer::class,
	\DIServer\Interfaces\ITaskServer::class     => \DIServer\Swoole\TaskServer::class
];