<?php

namespace DIServer\Services;

use DIServer\Interfaces\IService;

/**
 * 服务组件抽象类，所有的组件都继承自它。
 *
 * @author Back
 */
abstract class Service implements IService
{
	/**
	 * 倒置单例的调用方法，提供简易入口。
	 *
	 * @return Service
	 */
	public static function Instance($key = null)
	{
		return Container::GetInstance(static::class, $key);
	}


}