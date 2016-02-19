<?php
//应用程序启动前要注册的服务（全局有效，不可热重载）
return [
	\DIServer\Interfaces\IBootstrapper::class          => DIServer\Services\Bootstrapper::class,
	\DIServer\Interfaces\IEvent::class                 => DIServer\Event\Base::class,
	\DIServer\Interfaces\ILog::class                   => DIServer\Log\DILog::class,
	\DIServer\Interfaces\Swoole\ISwooleProxy::class   => \DIServer\Swoole\SwooleProxy::class,
	\DIServer\Interfaces\Swoole\IMasterServer::class  => \DIServer\Swoole\MasterServer::class,
	\DIServer\Interfaces\Swoole\IManagerServer::class => \DIServer\Swoole\ManagerServer::class,
	\DIServer\Interfaces\ISession::class              => DIServer\Session\File::class
];