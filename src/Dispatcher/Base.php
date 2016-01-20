<?php

namespace DIServer\Dispatcher;


use DIServer\Interfaces\IDispatcher;
use DIServer\Interfaces\IRequest;
use DIServer\Interfaces\IHandler;
use DIServer\Services\HandlerManager;
use DIServer\Services\Log;
use DIServer\Services\Service;

class Base extends Service implements IDispatcher
{
	protected $handlerManager;

	public function __construct(\DIServer\Interfaces\IApplication $app, HandlerManager $handlerManager)
	{
		parent::__construct($app);
		$this->handlerManager = $handlerManager;
	}

	public function Dispatch(IRequest $request)
	{
		$this->handle($request);
	}

	protected function handle(IRequest $request)
	{
		if(static::isLegal($request))
		{
			$handlerID = self::unpackHandlerID($request);
			$handlers = HandlerManager::GetHandlerByID($handlerID);
			if(is_array($handlers))
			{
				foreach($handlers as $handler)
				{
					$handler->BeforeHandle($request);
					$handler->Handle($request);
					$handler->AfterHandle($request);
				}
			}
		}
	}

	protected function isLegal(IRequest $request)
	{
		$data = $request->GetData();

		return strlen($data) >= 8;
	}

	protected function unpackHandlerID(IRequest $request)
	{
		$data = $request->GetData();
		$handlerID = null;
		if(strlen($data) >= 8)
		{
			$handlerID = unpack('x4/i1HandlerID', $data);
			if($handlerID)
			{
				$handlerID = array_pop($handlerID);
			}
		}

		return $handlerID;
	}
}