<?php

namespace DIServer\MiddleWare;

use DIServer\Services\Log;
use DIServer\Interfaces\IMiddleware;
use DIServer\Interfaces\IRequest;

class Session implements IMiddleware
{

	/**
	 * @param \DIServer\Interfaces\IRequest $request 当前请求
	 * @param \Closure                      $next    下一个中间件的方法引用
	 */
	public function Handle(IRequest $request, \Closure $next)
	{
		Log::Debug('Session Middleware Start');
		//\DIServer\Services\Session::Start($request->GetFD());

		$response = $next($request);

		Log::Debug('Session Middleware Close');
		//\DIServer\Services\Session::Close($request->GetFD());

		return $response;
	}
}