<?php

namespace DIServer\MiddleWare;

use DIServer\Interfaces\IMiddleware;
use DIServer\Interfaces\IRequest;
use DIServer\Services\Log;

class Authentication implements IMiddleware
{
	
	/**
	 * @param \DIServer\Interfaces\IRequest $request 当前请求
	 * @param \Closure                      $next    下一个中间件的方法引用
	 */
	public function Handle(IRequest $request, \Closure $next)
	{
		// TODO: Implement Handle() method.
		if(rand(0, 100) > 80)
		{
			call_user_func($next, $request);
		}
		else
		{
			Log::Debug("Auth failed.");
		}

	}
}