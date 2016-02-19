<?php

namespace DIServer\Services;

/**
 * 配置文件服务抽象类
 *
 * @package DIServer\Services
 */
class Config extends Facade
{
	protected static function getFacadeAccessor()
	{
		return \DIServer\Interfaces\IConfig::class;
	}

	/**
	 * 检查是否存在指定的配置项
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public static function Has($key)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 获取指定的配置项
	 *
	 * @param      $key
	 * @param null $default
	 *
	 * @return mixed
	 */
	public static function Get($key, $default = null)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 获取所有的配置
	 *
	 * @return array
	 */
	public static function All()
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 设置指定的配置项
	 *
	 * @param      $key
	 * @param null $value
	 *
	 * @return mixed
	 */
	public static function Set($key, $value = null)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}
}