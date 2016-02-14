<?php
/**
 * Created by PhpStorm.
 * User: Back
 * Date: 2015/12/26
 * Time: 2:12
 */

namespace DIServer\Bootstraps;

use DIServer\Services\Bootstrap;
use DIServer\Services\Application;

class RegisterServices extends Bootstrap
{
	public function Bootstrap()
	{

		/** @var array $servicesConfig */
		$servicesConfig = include DI_REGISTRY_PATH . '/Server.php';
		Application::AutoRegistry($servicesConfig);
		$this->setAlias();
	}


	protected function loadService($servicesConfig = [])
	{
		foreach($servicesConfig as $iface => $imp)
		{
			Application::RegisterClass($imp);
			if(Application::IsAbstract($iface))
			{
				Application::RegisterInterfaceByClass($iface, $imp);
			}
		}
	}

	protected function setAlias()
	{

	}
}