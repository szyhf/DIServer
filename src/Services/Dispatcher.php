<?php

namespace DIServer\Services;

use DIServer\Interfaces\IDispatcher;
use DIServer\Interfaces\IRequest;

class Dispatcher extends Facade
{
	public static function getFacadeAccessor()
	{
		return IDispatcher::class;
	}

	public static function Dispatch(IRequest $request)
	{
		self::getFacadeRoot()->Dispatch($request);
	}
}