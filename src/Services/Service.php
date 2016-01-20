<?php

namespace DIServer\Services;

use \DIServer\Interfaces\IApplication;
use DIServer\Interfaces\IService;

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

	///**
	// * 注册当前服务
	// */
	//public function Register()
	//{
	//	//echo "Service register " . get_class($this) . "\n";
	//	if(!$this->getApp()
	//	         ->IsRegistered(get_class($this))
	//	)
	//	{
	//		//echo "Auto register service {".get_class($this)."}\n";
	//		$this->getApp()
	//		     ->RegisterClass(get_class($this));
	//	}
	//}

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

	/**
	 * 倒置单例的调用方法，提供简易入口。
	 *
	 * @return Service
	 */
	public static function Instance($key = null)
	{
		return \DIServer\Container\Container::Instance()
		                                    ->GetInstance(static::class, $key);
	}
}