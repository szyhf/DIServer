<?php

namespace DIServer\Services;

use \DIServer\Interfaces\IApplication as IApplication;
use DIServer\Interfaces\Services\IService;

/**
 * Description of Service
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

	}

	/**
	 * 获取当前主程
	 *
	 * @return \DIServer\Interfaces\Application
	 */
	protected function getApp()
	{
		return $this->app;
	}

	/**
	 * @param \DIServer\Interfaces\Application $app
	 */
	protected function setApp(Application $app)
	{
		$this->app = $app;
	}

}
