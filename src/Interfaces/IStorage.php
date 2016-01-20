<?php

namespace DIServer\Interfaces;

/**
 * 基本的KV数据结构访问接口
 *
 * @package DIServer\Interfaces
 */
interface IStorage extends \ArrayAccess
{
	/**
	 * 检查是否存在指定的配置项
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function Has($key);

	/**
	 * 获取指定的配置项
	 *
	 * @param      $key
	 * @param null $default
	 *
	 * @return mixed
	 */
	public function Get($key, $default = null);

	/**
	 * 获取所有的配置
	 *
	 * @return array
	 */
	public function All();

	/**
	 * 设置指定的配置项
	 *
	 * @param      $key
	 * @param null $value
	 *
	 * @return mixed
	 */
	public function Set($key, $value = null);

	/**
	 * 向指定配置末端添加一个子项
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public function Push($key, $value);

	/**
	 * 向指定配置项第一个位置插入一个子项
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public function Prepend($key, $value);
}