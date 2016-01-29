<?php

namespace DIServer\Interfaces;


Interface IEvent
{
	/**
	 * 添加某个行为到指定标签
	 *
	 * @param string $tag      标签名
	 * @param mixed  $behavior 行为定义（is_callable为true即可），可以是'函数名'、[对象,'方法名’]、闭包方法
	 *
	 * @throws \Exception
	 */
	function Add($tag, $behavior);

	/**
	 * 添加一系列行为到指定标签
	 *
	 * @param string $tag       标签名
	 * @param array  $behaviors 行为数组
	 */
	function AddRange($tag, array $behaviors);

	/**
	 * 获取某个标签的所有行为
	 *
	 * @param $tag
	 *
	 * @return array
	 */
	function Get($tag = null);

	/**
	 * 触发事件
	 *
	 * @param string $tag    标签名
	 * @param mixed  $params 以引用方式传递于此标签行为的参数数组
	 */
	function Listen($tag, &$params = []);
}