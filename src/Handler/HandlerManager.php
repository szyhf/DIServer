<?php

namespace DIServer\Handler;


use DIServer\Helpers\Ary;
use DIServer\Interfaces\IHandler;
use DIServer\Interfaces\IHandlerManager;
use DIServer\Interfaces\IMiddleware;
use DIServer\Services\Application;
use DIServer\Services\Container;
use DIServer\Pipeline\Base as Pipeline;
use DIServer\Services\Log;

class HandlerManager implements IHandlerManager
{
	protected $handlers = [];

	public function GetHandlerByID($handlerID)
	{
		return $this->handlers[$handlerID];
	}

	public function __construct()
	{
		$handlers = Application::AutoBuildCollection('Handler.php');
		foreach($handlers as $key => $handler)
		{
			if(is_array($handler))
			{
				$this->handlers[$key] = $this->_createPipeClosure($handler);
			}
			else
			{
				$this->handlers[$key] = [$this->_createPipeClosure($handler)];
			}
		}
	}

	/**
	 * @param \DIServer\Interfaces\IHandler $handler
	 *
	 * @return \Closure
	 */
	private function _createPipeClosure(\DIServer\Interfaces\IHandler $handler)
	{
		$pipeline = new Pipeline();
		$middlewareClasses = $handler->GetMiddlewares();
		$middlewareHandlers = [];
		foreach($middlewareClasses as $middlewareClass)
		{
			$refClass = new \ReflectionClass($middlewareClass);
			if(!$refClass->isSubclassOf(IMiddleware::class))
			{
				Log::Warning("Try to load $middlewareClass in " . get_class($handler) . " but is not instance of IMiddleware");
				continue;
			}
			$middlewareHandlers[] = Container::BuildWithClass($middlewareClass);
		}

		return $pipeline->Through($middlewareHandlers)
		                ->Prepared(function ($request) use ($handler)
		                {
			                //最后一层封装为Handler的默认Handle方法
			                return Container::CallMethod($handler, 'Handle', ['request' => $request]);
		                });
	}
}