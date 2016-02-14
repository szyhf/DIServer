<?php

namespace DIServer\Interfaces;


Interface IFilter
{
	/**
	 * @param \DIServer\Interfaces\IRequest $request
	 *
	 * @return bool return FALSE will interrupt the filter chain.
	 */
	public function Filtering(\DIServer\Interfaces\IRequest $request);
}