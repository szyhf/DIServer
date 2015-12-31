<?php

namespace DIServer\Services;

use \DIServer\Interfaces\IApplication as IApplication;
use DIServer\Interfaces\Services\IService;

/**
 * 服务组件抽象类，所有的组件都继承自它。
 *
 * @author Back
 */
abstract class Service implements IService
{
	/**
	 * 当前主程
	 *
	 * @var \DIServer\Interfaces\IApplication
	 */
	private $app;

	/**
	 * IService constructor.
	 *
	 * @param \DIServer\Interfaces\IApplication $app
	 */
	public function __construct(IApplication $app)
	{
		$this->setApp($app);
	}

	/**
	 * 注册当前服务
	 */
	public function Register()
	{
		echo "Service register ".get_class($this)."\n";
	}

	/**
	 * 获取当前主程
	 *
	 * @return \DIServer\Interfaces\IApplication
	 */
	protected function getApp()
	{
		return $this->app;
	}

	/**
	 * @param \DIServer\Interfaces\IApplication $app
	 */
	protected function setApp(IApplication $app)
	{
		$this->app = $app;
	}

}
