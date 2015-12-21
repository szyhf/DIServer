<?php
namespace DIServer;

use DIServer\Lib\DI\DIContainer\DIContainer;

/**
 * Description of NewDIServer
 * @author Back
 */
class NewDIServer
{
	/**
	 * 默认容器
	 * @var DIServer\Lib\DI\DIContainer\DIContainer
	 */
	private $container;

	public function __construct(DIContainer $container)
	{
		$this->container = $container;

		/* @var $register DIServer\Lib\DI\AutoRegister */
		$register = $this->container->BuildWithClass('DIServer\Lib\DI\AutoRegister');
		$register->Register(__DIR__.'/Lib/DI');
	}
}
