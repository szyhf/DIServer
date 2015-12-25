<?php

$servicesNamespace = '\DIServer\Services';
$ifaceNamespace = '\DIServer\Interfaces';
//这里提供服务的基本映射
return [
    $ifaceNamespace . '\IBootstrapper' => $servicesNamespace . '\Bootstrapper',
    $ifaceNamespace . '\IProcessManager' => 'DIServer\ProcessManager',
    //swoole服务注册
    $ifaceNamespace . '\IMasterServer' => $servicesNamespace . '\MasterServer',
    $ifaceNamespace . '\IManagerServer' => $servicesNamespace . '\ManagerServer',
    $ifaceNamespace . '\IWorkerServer' => $servicesNamespace . '\WorkerServer',
    $ifaceNamespace . '\ITaskServer' => $servicesNamespace . '\TaskServer',
];
