<?php

namespace DIServer\Interfaces;


interface IMiddleware
{
	/**
	 * @param \DIServer\Interfaces\IRequest $request 当前请求
	 * @param \Closure                      $next    下一个中间件的方法引用
	 */
	public function Handle(IRequest $request, \Closure $next);
}