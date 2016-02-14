<?php

namespace DIServer\Handler;


use DIServer\Interfaces\IHandler;
use DIServer\Interfaces\IHandlerManager;
use DIServer\Services\Application;

class HandlerManager implements IHandlerManager
{
	
	public function GetHandlerByID($handlerID)
	{
		return $this->handlers[$handlerID];
	}

	public function __construct()
	{
		$this->_reloadCommonHandler();
		$this->_reloadServerHandler();
	}

	/**
	 * 重载Common/Handler
	 */
	private function _reloadCommonHandler()
	{
		$path = Application::GetCommonPath() . '/Registry/Handler.php';
		if(file_exists($path))
		{
			$handlerClasses = include $path;
			//Log::Debug($handlerClasses);
			$this->_loadHandler($handlerClasses);
		}
	}

	/**
	 * 重载Server/Handler
	 */
	private function _reloadServerHandler()
	{
		$path = Application::GetServerPath() . '/Registry/Handler.php';
		if(file_exists($path))
		{
			$handlerClasses = include $path;
			//Log::Debug($handlerClasses);
			$this->_loadHandler($handlerClasses);
		}
	}

	private function _loadHandler(array $handlerClasses)
	{
		foreach($handlerClasses as $handlerID => $handlerClassAry)
		{
			if(is_array($handlerClassAry))
			{
				foreach($handlerClassAry as $handlerClass)
				{
					$this->_tryRegitryHandler($handlerID, $handlerClass);
				}
			}
			else
			{
				$this->_tryRegitryHandler($handlerID, $handlerClassAry);
			}
		}
	}

	private function _tryRegitryHandler($handlerID, $handlerClassName)
	{
		if(class_exists($handlerClassName))
		{
			$handlerRefClass = new \ReflectionClass($handlerClassName);
			if($handlerRefClass->isSubclassOf(IHandler::class))
			{
				Application::RegisterClass($handlerClassName);
				Application::RegisterInterfaceByClass(IHandler::class, $handlerRefClass->getName(), $handlerRefClass->getName());
				$handlerObj = Application::GetInstance(IHandler::class, $handlerRefClass->getName());
				$this->handlers[$handlerID][] = $handlerObj;
			}
			else
			{
				Log::Warning("Load $handlerClassName is not instance of IHandler.");
			}
		}
		else
		{
			Log::Warning("Try to load $handlerClassName but not exist.");
		}
	}
}