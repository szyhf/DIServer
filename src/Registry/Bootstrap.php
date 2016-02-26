<?php
//主进程控制启动器的加载及加载顺序
return [
	\DIServer\Bootstraps\CheckEnvironment::class,
	\DIServer\Bootstraps\DetectEnvironment::class,
	//\DIServer\Bootstraps\AutoBuilder::class,
	\DIServer\Bootstraps\InitSwooleServer::class,
	\DIServer\Bootstraps\SwooleSetting::class,
	\DIServer\Bootstraps\AddProcess::class,
	\DIServer\Bootstraps\InitMonitor::class,
	\DIServer\Bootstraps\Start::class
];