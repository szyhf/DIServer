<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer;

/**
 *
 * @author Back
 */
interface IUdpProtocol
{
    /**
     * 接收到一个Udp数据包时被触发
     * @param \swoole_server $server 当前进程的swoole_server对象
     * @param string $data 接受到的数据包
     * @param array $client_info 客户端信息
     */
    public function OnPacket(\swoole_server &$server, $data, $client_info);
}
