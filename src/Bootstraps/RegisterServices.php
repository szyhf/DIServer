<?php
/**
 * Created by PhpStorm.
 * User: Back
 * Date: 2015/12/26
 * Time: 2:12
 */

namespace DIServer\Bootstraps;

use DIServer\Services\Bootstrap;

class RegisterServices extends Bootstrap
{
	public function Bootstrap()
	{

		/** @var array $servicesConfig */
		$servicesConfig = include DI_REGISTRY_PATH . '/Server.php';
		$this->getApp()->AutoRegistry($servicesConfig);
		$this->setAlias();
	}


	protected function loadService($servicesConfig = [])
	{
		foreach($servicesConfig as $iface => $imp)
		{
			$this->getApp()
			     ->RegisterClass($imp);
			if($this->getApp()
			        ->IsAbstract($iface)
			)
			{
				$this->getApp()
				     ->RegisterInterfaceByClass($iface, $imp);
			}
		}
	}

	protected function setAlias()
	{

	}
}