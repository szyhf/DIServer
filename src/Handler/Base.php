<?php

namespace DIServer\Handler;

use DIServer\Interfaces\IApplication;
use DIServer\Interfaces\IHandler;
use DIServer\Interfaces\IMiddleware;
use DIServer\Interfaces\IRequest;
use DIServer\Services\Container;
use DIServer\Pipeline\Base as Pipeline;
use DIServer\Services\Log;
use DIServer\Services\Service;

abstract class Base extends Service implements IHandler
{
	private $_startTime;
	private $_endTime;
	/**
	 * @var \Closure 处理Request的封装函数（根据Middleware和Handler生成）
	 */
	protected $dispatchRequestClosure;

	public function __construct()
	{
		$pipeline = new Pipeline();
		$middlewareClasses = $this->GetMiddlewares();
		$middlewareHandlers = [];
		foreach($middlewareClasses as $middlewareClass)
		{
			$refClass = new \ReflectionClass($middlewareClass);
			if(!$refClass->isSubclassOf(IMiddleware::class))
			{
				Log::Debug("Try to load $middlewareClass in " . get_class($this) . " but is not instance of IMiddleware");
				continue;
			}
			$middlewareHandlers[] = Container::BuildWithClass($middlewareClass);
		}
		$this->dispatchRequestClosure = $pipeline->Through($middlewareHandlers)
		                                         ->Prepared(function ($request)
		                                         {
			                                         //最后一层封装为Handler的默认Handle方法
			                                         return Container::CallMethod($this, 'Handle', ['request' => $request]);
		                                         });
	}

	public function DispatchRequest($request)
	{
		call_user_func($this->dispatchRequestClosure, $request);
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

	public function GetMiddlewares()
	{
		return [
		];
	}
}