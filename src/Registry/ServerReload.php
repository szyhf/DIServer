<?php
return [
		\DIServer\Interfaces\Swoole\IWorkerServer::class     => \DIServer\Swoole\WorkerServer::class,
		\DIServer\Interfaces\Swoole\ITaskWorkerServer::class => \DIServer\Swoole\TaskWorkerServer::class,
];