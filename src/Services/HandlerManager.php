<?php

namespace DIServer\Services;


use DIServer\Interfaces\IHandler;
use DIServer\Interfaces\IRequest;

class HandlerManager extends Facade
{
	protected $handlers;

	public static function getFacadeAccessor()
	{
		return \DIServer\Interfaces\IHandlerManager::class;
	}


	/**
	 * @return \IHandlerManager
	 */
	public static function getFacadeRoot()
	{
		return parent::getFacadeRoot();
	}

	/**
	 * @param          $handlerID
	 *
	 * @return IHandler
	 */
	public static function GetHandlerByID($handlerID)
	{
		/** @var HandlerManager $instance */
		$instance = self::Instance();

		return $instance->GetHandler($handlerID);
	}

	public function GetHandler($handlerID)
	{
		return $this->handlers[$handlerID];
	}

	public function __construct(\DIServer\Interfaces\IApplication $app)
	{
		parent::__construct($app);
		$this->_reloadCommonHandler();
		$this->_reloadServerHandler();
	}

	/**
	 * 重载Common/Handler
	 */
	private function _reloadCommonHandler()
	{
		$path = $this->getApp()
		             ->GetCommonPath() . '/Registry/Handler.php';
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
		$path = $this->getApp()
		             ->GetServerPath() . '/Registry/Handler.php';
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
				$this->getApp()
				     ->RegisterClass($handlerClassName);
				$this->getApp()
				     ->RegisterInterfaceByClass(IHandler::class, $handlerRefClass->getName(), $handlerRefClass->getName());
				$handlerObj = $this->getApp()
				                   ->GetInstance(IHandler::class, $handlerRefClass->getName());
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