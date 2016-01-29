<?php

namespace DIServer\Event;


use DIServer\Event\BehaviorNotCallableException;
use DIServer\Services\Log;

class Base
{
	protected $events = [];

	/**
	 * 添加某个行为到指定标签
	 *
	 * @param string $tag      标签名
	 * @param mixed  $behavior 行为定义（is_callable为true即可），可以是'函数名'、[对象,'方法名’]、闭包方法
	 *
	 * @throws \Exception
	 */
	public function Add($tag, $behavior)
	{
		if(is_callable($behavior))
		{
			$this->events[$tag][] = $behavior;
		}
		else
		{
			throw new BehaviorNotCallableException($tag, $behavior);
		}
	}

	/**
	 * 添加一系列行为到指定标签
	 *
	 * @param string $tag       标签名
	 * @param array  $behaviors 行为数组
	 */
	public function AddRange($tag, array $behaviors)
	{
		foreach($behaviors as $behavior)
		{
			$this->Add($tag, $behavior);
		}
	}


	/**
	 * 获取某个标签的所有行为
	 *
	 * @param $tag
	 *
	 * @return array
	 */
	public function Get($tag = null)
	{
		$res = $tag === null ? $this->events : $this->events[$tag];
		Log::Debug($this->events);
		return $res;
	}

	/**
	 * 触发事件
	 *
	 * @param string $tag    标签名
	 * @param mixed  $params 以引用方式传递于此标签行为的参数数组
	 */
	public function Listen($tag, &$params = [])
	{
		if(isset($this->events[$tag]))
		{
			$startTime = microtime(true);
			foreach($this->events[$tag] as $event)
			{
				$res = call_user_func_array($event, $params);
				if($res === false)
				{
					break;//中断执行
				}
			}

			$endTime = microtime(true);
			$costTime = $startTime - $endTime;
			if($costTime >= 1000)
			{
				Log::Warning("Call $tag event cost {$costTime}ms.");
			}
		}
	}
}