<?php
//Worker进程启动时需要注册的服务
return [
	DIServer\Interfaces\IDispatcher::class     => DIServer\Dispatcher\WorkerDispatcher::class,
	DIServer\Interfaces\IHandlerManager::class => DIServer\Handler\HandlerManager::class,
];