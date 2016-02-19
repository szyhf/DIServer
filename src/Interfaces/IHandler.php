<?php

namespace DIServer\Interfaces;


interface IHandler extends IService
{
	public function BeforeHandle(IRequest $request);

	public function Handle(IRequest $request);

	public function AfterHandle(IRequest $request);

	/**
	 * @return array 获取该Handler的所有中间件名称（按调用顺序）
	 */
	public function GetMiddlewares();
}