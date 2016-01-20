<?php

namespace DIServer\Interfaces;


interface IHandler extends IService
{
	public function BeforeHandle(IRequest $request);
	public function Handle(IRequest $request);
	public function AfterHandle(IRequest $request);
}