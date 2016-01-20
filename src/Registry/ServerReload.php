<?php
return [
	\DIServer\Interfaces\Swoole\IWorkerServer::class => \DIServer\Swoole\WorkerServer::class,
	\DIServer\Interfaces\Swoole\ITaskServer::class   => \DIServer\Swoole\TaskServer::class,
];