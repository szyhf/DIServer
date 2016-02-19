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
    public function OnMasterStart(\swoole_server $server);

    /**
     * 服务结束时触发
     * @param \swoole_server $server
     */
    public function OnMasterShutdown(\swoole_server $server);
}
