<?php

namespace DIServer\Interfaces;


interface IRequest
{
	/**
	 * 获取当前请求的完整数据包
	 *
	 * @return string
	 */
	function GetData();

	/**
	 * fd是tcp连接的文件描述符，在swoole_server中是客户端的唯一标识符，超过1600万后会自动从1开始进行复用
	 *
	 * @return int
	 */
	function GetFD();

	/**
	 * from_id是来自于哪个reactor线程
	 *
	 * @return int
	 */
	function GetFromID();

	/**
	 * 客户端的IP地址“xxx.xxx.xxx.xxx”
	 *
	 * @return string
	 */
	function GetRemoteIP();

	/**
	 * 接受该请求的服务器端口
	 *
	 * @return int
	 */
	function GetServerPort();

	/**
	 * 客户端的端口
	 *
	 * @return int
	 */
	function GetRemotePort();

	/**
	 * 客户端连接的时间戳
	 *
	 * @return int
	 */
	function GetConnectTime();

	/**
	 * 上次收到客户端消息的时间戳
	 *
	 * @return int
	 */
	function GetLastTime();
}