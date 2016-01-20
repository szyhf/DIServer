<?php
namespace DIServer\Interfaces;

interface IService
{
	///**
	// * @return mixed
	// */
	//public function Register();

	/**
	 * 获取当前类型的注册实例或者默认单例
	 * @return Service
	 */
	public static function Instance($key = null);
}