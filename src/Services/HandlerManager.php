<?php

namespace DIServer\Services;


use DIServer\Interfaces\IHandler;
use DIServer\Interfaces\IRequest;
use DIServer\Services\Application;

class HandlerManager extends Facade
{
	protected $handlers;

	public static function getFacadeAccessor()
	{
		return \DIServer\Interfaces\IHandlerManager::class;
	}

	/**
	 * @param          $handlerID
	 *
	 * @return IHandler
	 */
	public static function GetHandlerByID($handlerID)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}
}