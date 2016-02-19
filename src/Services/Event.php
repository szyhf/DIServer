<?php

namespace DIServer\Services;


class Event extends Facade
{
	public static function getFacadeAccessor()
	{
		return \DIServer\Interfaces\IEvent::class;
	}

	/**
	 * 添加某个行为到指定标签
	 *
	 * @param string $tag      标签名
	 * @param mixed  $behavior 行为定义（is_callable为true即可），可以是'函数名'、[对象,'方法名’]、闭包方法
	 *
	 * @throws \Exception
	 */
	public static function Add($tag, $behavior)
	{
		//Debug参考，每次注册事件时都会打印，慎用！！
		//建议调用前先写部分过滤
		//$caller = current(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT,1));
		//$file = str_replace(self::GetAppStatic()
		//                        ->GetFrameworkPath(), '', $caller['file']);
		//Log::Debug("Add tag '$tag' in $file on line {$caller['line']}");
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 添加一系列行为到指定标签
	 *
	 * @param string $tag       标签名
	 * @param array  $behaviors 行为数组
	 */
	public static function AddRange($tag, array $behaviors)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 获取某个标签的所有行为
	 *
	 * @param $tag
	 *
	 * @return array
	 */
	public static function Get($tag = null)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 触发事件
	 *
	 * @param string $tag    标签名
	 * @param mixed  $params 以引用方式传递于此标签行为的参数数组
	 */
	public static function Listen($tag, $params = [], $line = __LINE__)
	{
		//Debug参考，每次触发事件时都会打印，慎用！！
		//建议调用前先写部分过滤
		//$caller = current(debug_backtrace());
		//Log::Debug("Listen to '$tag' in {$caller['file']} on line {$caller['line']}");
		return static::__callStatic(__FUNCTION__, func_get_args());
	}
}