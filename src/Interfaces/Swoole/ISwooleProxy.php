<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Diserver\Interfaces\Swoole;
use DIServer\Interfaces\Services\IService;

/**
 *
 * @author Back
 */
interface ISwooleProxy extends IService
{

    public function OnStart(\swoole_server $server);

    public function OnShutdown(\swoole_server $server);

    public function OnWorkerStart(\swoole_server $server, $worker_id);

    public function OnWorkerError(\swoole_server $serv, $worker_id, $worker_pid, $exit_code);

    public function OnWorkerStop(\swoole_server $server, $worker_id);

    public function OnConnect(\swoole_server $server, $fd, $from_id);

    public function OnClose($server, $fd, $from_id);

    public function OnReceive(\swoole_server $server, $fd, $from_id, $data);

    public function OnTask(\swoole_server $server, $task_id, $from_id, $param);

    public function OnFinish(\swoole_server $server, $task_id, $taskResult);

    public function OnPipeMessage(\swoole_server $server, $from_worker_id, $message);

    public function OnPacket(\swoole_server &$server, $data, $client_info);
}
