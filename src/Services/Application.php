<?php

namespace DIServer\Services;

use DIServer\Interfaces\IApplication;

class Application extends Facade
{
	public static function getFacadeAccessor()
	{
		return IApplication::class;
	}
	
	/**
	 * 应用程序目录
	 *
	 * @return string
	 */
	public static function GetBasePath($addPath = '')
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}
	
	/**
	 * 应用程序目录
	 *
	 * @return string
	 */
	public static function GetServerPath($addPath = '')
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}
	
	/**
	 * 获取DIServer框架目录
	 *
	 * @return string
	 */
	public static function GetFrameworkPath($addPath = '')
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}
	
	/**
	 * 获取服务名称
	 *
	 * @return string
	 */
	public static function GetServerName()
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}
	
	/**
	 * 获取公共目录路径
	 *
	 * @return string
	 */
	public static function GetCommonPath()
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}
	
	/**
	 * 启动应用程序
	 */
	public static function Start()
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}
	
	/**
	 * 自动注册（快捷工具）
	 *
	 * @param            $registry
	 * @param bool|false $build
	 *
	 * @return array
	 * @throws \DIServer\Container\NotExistException
	 * @throws \DIServer\Container\NotRegistedException
	 * @throws \DIServer\Container\RegistedException
	 */
	public static function AutoRegistry($registryPath, $build = false)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	public static function AutoBuildCollection($registryFile, $iface = '')
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 按照惯例顺序获得FrameworkPath、CommonPath、ServerPath
	 *
	 * @param $addPath
	 *
	 * @return array
	 */
	public static function GetConventionPaths($addPath)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}
}