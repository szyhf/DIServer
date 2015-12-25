<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer;

/**
 * Tcp服务的回调接口
 * @author Back
 */
interface ITcpProtocol
{
    /**
     * 新建了一个Tcp连接时触发
     * @param \swoole_server $server 当前进程的swoole_server对象
     * @param int $fd 当前连接的文件描述符（惟一）
     * @param int $from_id 当前连接的Rector线程
     */
    public function OnConnect(\swoole_server &$server, $fd, $from_id);
    
    /**
     * 关闭了一个Tcp连接时触发
     * @param \swoole_server $server 当前进程的swoole_server对象
     * @param int $fd 当前连接的文件描述符（惟一）
     * @param int $from_id 当前连接的Rector线程
     */
    public function OnClose(\swoole_server &$server, $fd, $from_id);
    
    /**
     * 接受到一个Tcp客户端发来的数据时触发
     * @param \swoole_server $server 当前进程的swoole_server对象
     * @param int $fd 当前连接的文件描述符（惟一）
     * @param int $from_id 当前连接的Rector线程
     * @param string $data 接收到的数据（如果没有设置包头\拆包协议，可能收到的数据会不完整或者黏包）
     */
    public function OnReceive(\swoole_server &$server, $fd, $from_id, &$data);
}
