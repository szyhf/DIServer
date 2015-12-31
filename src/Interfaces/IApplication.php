<?php
/**
 * Created by PhpStorm.
 * User: Back
 * Date: 2015/12/28
 * Time: 19:23
 */

namespace DIServer\Interfaces;


use DIServer\Interfaces\Container\IContainer;

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
	 * 启动应用程序
	 */
	public function Start();


}