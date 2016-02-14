<?php

namespace DIServer\Services;

use DIServer\Request\Base;
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
			$instance = Container::BuildWithClass(Base::class, [
				'fd'     => $fd,
				'fromID' => $fromID,
				'data'   => $data
			]);
			Container::Unregister(IRequest::class);
			Container::RegisterInterfaceByInstance(IRequest::class, $instance);

			return $instance;
		}
	}
}