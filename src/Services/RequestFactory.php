<?php

namespace DIServer\Services;


use DIServer\Container\Container;
use DIServer\Request\Request;
use DIServer\Interfaces\IRequest;

class RequestFactory extends Service
{
	/**
	 * @param       $fd
	 * @param       $fromID
	 * @param       $data
	 * @param array $clientInfo
	 *
	 * @return IRequest
	 * @throws \DIServer\Container\MakeFailedException
	 */
	public static function Make($fd, $fromID, $data, $clientInfo = null)
	{
		if(!$clientInfo)
		{
			$instance = Container::Instance()
			                     ->BuildWithClass(Request::class, [
				                     'fd'     => $fd,
				                     'fromID' => $fromID,
				                     'data'   => $data
			                     ]);
			Container::Instance()
			         ->Unregister(IRequest::class);
			Container::Instance()
			         ->RegisterInterfaceByInstance(IRequest::class, $instance);

			return $instance;
		}
	}
}