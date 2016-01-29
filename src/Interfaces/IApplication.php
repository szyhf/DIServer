<?php
/**
 * Created by PhpStorm.
 * User: Back
 * Date: 2015/12/28
 * Time: 19:23
 */

namespace DIServer\Interfaces;

interface IApplication extends IContainer
{

	/**
	 * 应用程序目录
	 *
	 * @return string
	 */
	public function GetBasePath();

	/**
	 * 应用程序目录
	 *
	 * @return string
	 */
	public function GetServerPath();

	/**
	 * 获取DIServer框架目录
	 *
	 * @return string
	 */
	public function GetFrameworkPath();

	/**
	 * 获取服务名称
	 *
	 * @return string
	 */
	public function GetServerName();

	/**
	 * 获取公共目录路径
	 *
	 * @return string
	 */
	public function GetCommonPath();

	/**
	 * 启动应用程序
	 */
	public function Start();

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
	public function AutoRegistry($registry, $build = false);
}