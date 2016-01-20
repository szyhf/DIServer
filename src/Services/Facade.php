<?php

namespace DIServer\Services;

use DIServer\Container\Container;

/**
 * 参考Lavarel实现的简易Facade类
 *
 * @package DIServer\Services
 */
abstract class Facade extends Service
{
	/**
	 * @return Facade
	 */
	protected static function getFacadeRoot()
	{
		static $instance = null;
		if(!$instance)//只从容器获取一次，减少搜索的次数
		{
			$instance = Container::Instance()
			                     ->GetInstance(Static::getFacadeAccessor());
		}
		return $instance;
	}

	protected static function getFacadeAccessor()
	{
		//默认方案，欢迎重载
		return static::class;
	}

	/**
	 * Handle dynamic, static calls to the object.
	 *
	 * @param  string $method
	 * @param  array  $args
	 *
	 * @return mixed
	 */
	public static function __callStatic($method, $args)
	{
		$instance = static::getFacadeRoot();

		if(!$instance)
		{
			Log::Critical("A facade call to $method has not set instance.");
		}
		switch(count($args))
		{
			case 0:
				return $instance->$method();

			case 1:
				return $instance->$method($args[0]);

			case 2:
				return $instance->$method($args[0], $args[1]);

			case 3:
				return $instance->$method($args[0], $args[1], $args[2]);

			case 4:
				return $instance->$method($args[0], $args[1], $args[2], $args[3]);

			default:
				return call_user_func_array([$instance, $method], $args);
		}
	}
}