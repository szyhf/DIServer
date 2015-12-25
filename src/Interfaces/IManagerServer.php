<?php

namespace DIServer\Interfaces;

/**
 *
 * @author Back
 */
interface IManagerServer
{

    /**
     * 当管理进程启动时调用
     * @param \swoole_server $server
     */
    public function OnManagerStart(\swoole_server $server);

    /**
     * 当管理进程结束时调用
     * @param \swoole_server $server
     */
    public function OnManagerStop(\swoole_server $server);
}
