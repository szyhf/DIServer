<?php
//应用程序启动前要注册的服务（全局有效，不可热重载）
return [
	DIServer\Interfaces\IBootstrapper::class => DIServer\Services\Bootstrapper::class,
	DIServer\Interfaces\IEvent::class        => DIServer\Event\Base::class,
	DIServer\Interfaces\ILog::class          => DIServer\Log\DILog::class
];