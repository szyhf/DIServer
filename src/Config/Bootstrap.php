<?php
//控制启动器的加载及加载顺序
return [
    \DIServer\Bootstraps\CheckEnvironment::class,
    \DIServer\Bootstraps\DetectEnvironment::class,
    \DIServer\Bootstraps\InitLogging::class,
    \DIServer\Bootstraps\InitSwooleServer::class,    
    \DIServer\Bootstraps\SetSwooleConfig::class,
];