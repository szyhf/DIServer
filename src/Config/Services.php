<?php
	//这里提供服务的基本映射
	return [
		//    $ifaceNamespace . '\IBootstrapper' => $servicesNamespace . '\Bootstrapper',
		//    $ifaceNamespace . '\IProcessManager' => 'DIServer\ProcessManager',
		//swoole服务注册
		\DIServer\Interfaces\IMasterServer::class  => \DIServer\Services\MasterServer::class,
		\DIServer\Interfaces\IManagerServer::class => \DIServer\Services\ManagerServer::class,
		\DIServer\Interfaces\IWorkerServer::class  => \DIServer\Services\WorkerServer::class,
		\DIServer\Interfaces\ITaskServer::class    => \DIServer\Services\TaskServer::class,
	];