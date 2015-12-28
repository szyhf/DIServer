<?php

namespace DIServer\Interfaces\Swoole;

/**
 *
 * @author Back
 */
interface IMasterServer
{

    /**
     * 服务启动时触发
     * @param \swoole_server $server
     */
    public function OnStart(\swoole_server $server);

    /**
     * 服务结束时触发
     * @param \swoole_server $server
     */
    public function OnShutdown(\swoole_server $server);
}
