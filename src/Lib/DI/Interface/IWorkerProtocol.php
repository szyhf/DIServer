<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer;

/**
 * Worker进程或TaskWorker进程的基础回调接口
 * @author Back
 */
interface IWorkerProtocol
{
    /**
     * 进程启动时被触发
     * @param \swoole_server $server 当前进程的swoole_server对象
     * @param int $worker_id 当前进程的ID
     */
    public function OnWorkerStart(\swoole_server $server, $worker_id);
    
    /**
     * 进程发生错误时导致退出时触发（一般情况下，Manager会重新拉起一起新进程）
     * @param \swoole_server $server 当前进程的swoole_server对象
     * @param int $worker_id 故障进程的ID
     * @param int $worker_pid 故障进程的PID
     * @param int $exit_code 错误代码
     */
    public function OnWorkerError(\swoole_server $server, $worker_id, $worker_pid, $exit_code);
    
    /**
     * 进程正常结束时触发
     * @param \swoole_server $server 当前进程的swoole_server对象
     * @param type $worker_id 当前进程的ID
     */
    public function OnWorkerStop(\swoole_server $server, $worker_id);
    public function OnPipeMessage(\swoole_server $server, $from_worker_id, $message);
}
