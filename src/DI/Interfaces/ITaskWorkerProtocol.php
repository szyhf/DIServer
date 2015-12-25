<?php

namespace DIServer;

/**
 * TaskWorker回调接口
 * @author Back
 */
interface ITaskWorkerProtocol
{
    /**
     * 进程启动时被触发
     * @param \swoole_server $server 当前进程的swoole_server对象
     * @param int $worker_id 当前进程的ID
     */
    public function OnTaskWorkerStart(\swoole_server $server, $task_worker_id);
    
    /**
     * 进程发生错误时导致退出时触发（一般情况下，Manager会重新拉起一起新进程）
     * @param \swoole_server $server 当前进程的swoole_server对象
     * @param int $worker_id 故障进程的ID
     * @param int $worker_pid 故障进程的PID
     * @param int $exit_code 错误代码
     */
    public function OnTaskWorkerError(\swoole_server $server, $task_worker_id, $task_worker_pid, $exit_code);
    
    /**
     * 进程正常结束时触发
     * @param \swoole_server $server 当前进程的swoole_server对象
     * @param int $worker_id 当前进程的ID
     */
    public function OnTaskWorkerStop(\swoole_server $server, $worker_id);
    /**
     * TaskWorker收到任务时触发
     * @param \swoole_server $server 
     * @param int $task_id
     * @param int $from_id
     * @param mixed $param
     */
    public function OnTask(\swoole_server $server, $task_id, $from_id, $param);
    
    /**
     * Task一次工作结束以后，在Worker进程中被触发（如果在Task中执行了Return方法）
     * @param \swoole_server $server 当前进程的swoole_server对象
     * @param int $task_id 结束工作的Task的ID
     * @param mixed $taskResult 在OnTask方法中被Return的数据。
     */
    public function OnFinish(\swoole_server $server, $task_id, $taskResult);
}
