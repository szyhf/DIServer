<?php

namespace DIServer\Handler;

use DIServer\Interfaces\IApplication;
use DIServer\Interfaces\IHandler;
use DIServer\Interfaces\IRequest;
use DIServer\Services\Event;
use DIServer\Services\Log;
use DIServer\Services\Service;

abstract class Base extends Service implements IHandler
{
	private $_startTime;
	private $_endTime;

	public function __construct()
	{

	}

	public function BeforeHandle(IRequest $request)
	{
		$this->_startTime = microtime(true);
	}

	public function AfterHandle(IRequest $request)
	{
		$this->_endTime = microtime(true);
		if($this->_endTime - $this->_startTime > 1)
		{
			Log::Debug("Handler " . get_class($this) . " used " . ($this->_endTime - $this->_startTime . "s."));
		}
	}

	public function GetFilters()
	{
		return [
			\DIServer\Filter\Login::class
		];
	}
}