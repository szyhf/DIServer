<?php

namespace DIServer\Services;

use DIServer\Interfaces\IRequest;
use DIServer\Container\Container;
use DIServer\Interfaces\IApplication;

class Server extends Facade
{
	const SendFileLimit = 2097152;//大于2MB的数据应使用SendFile接口发送

	protected static function getFacadeAccessor()
	{
		return \swoole_server::class;
	}

	public static function Send($fd, $data)
	{
		/** @var \swoole_server $instance */
		$instance = self::getFacadeRoot();
		if(strlen($data) > self::SendFileLimit)
		{
			//Log::Debug("send file :" . strlen($data));
			$tempFilePath = Container::Instance()
			                         ->GetInstance(IApplication::class)
			                         ->GetServerPath() . '/Runtimes/Temp';
			//Log::Debug("send file :$tempFilePath");
			$fileName = md5(microtime() . strlen($data) . $fd);
			$filePath = "$tempFilePath/$fileName";
			//Log::Debug("send file :$filePath");
			file_put_contents($filePath, $data, LOCK_EX);
			$res = $instance->sendfile($fd, $filePath);
			unlink($filePath);

			return $res;
		}
		else
		{
			//Log::Debug("send ".substr($data,6)." to $fd");

			return $instance->send($fd, $data);
		}
	}

	/**
	 * 快捷方法，直接向请求方返回数据（通过进程内同步自动获取Request.fd)
	 *
	 * @param string $data
	 *
	 * @return bool 是否发送成功
	 * @throws \DIServer\Container\NotRegistedException
	 */
	public static function SendResponse($data)
	{
		$fd = Container::Instance()
		               ->GetInstance(IRequest::class)
		               ->GetFD();

		return self::Send($fd, $data);
	}

	public static function SendAll($fdList = [], $data)
	{
		$failedList = [];
		foreach($fdList as $fd)
		{
			if(!self::Send($fd, $data))
			{
				$failedList[] = $fd;
			}
		}

		return $failedList;
	}

	/**
	 * 向所有已连接的客户端发送消息
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public static function SendPublicMessage($data)
	{
		/** @var \swoole_server $instance */
		$instance = self::getFacadeRoot();
		$connections = $instance->connections;
		$failedList = [];
		foreach($connections as $fd)
		{
			if(!self::Send($fd, $data))
			{
				$failedList[] = $fd;
			}
		}

		return $failedList;
	}

	/**
	 * 投递UDP数据包
	 *
	 * @param string     $ip   形如"xxx.xxx.xxx.xxx"，可以省略开头的0
	 * @param int        $port 端口号[1,65535]
	 * @param string     $data 长度应小于64Kb
	 * @param bool|false $ipv6
	 */
	public static function SendTo($ip, $port, $data, $ipv6 = false)
	{
		/** @var \swoole_server $instance */
		$instance = self::getFacadeRoot();
		return $instance->sendto($ip, $port, $data, $ipv6);
	}

	public static function Close($fd, $from_id = 0)
	{
		/** @var \swoole_server $instance */
		$instance = self::getFacadeRoot();
		return $instance->close($fd, $from_id);
	}
}