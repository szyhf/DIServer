<?php

namespace DIServer\Interfaces;

/**
 * 负责将IRequest交给合适的Handler来处理
 *
 * @package DIServer\Interfaces
 */
interface IDispatcher
{
	/**
	 * @param \DIServer\Interfaces\IRequest $request
	 *
	 * @return mixed
	 */
	public function Dispatch(IRequest $request);
}