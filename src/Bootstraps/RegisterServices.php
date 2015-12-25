<?php
/**
 * Created by PhpStorm.
 * User: Back
 * Date: 2015/12/26
 * Time: 2:12
 */

namespace DIServer\Bootstraps;

use DIServer\Package;

class RegisterServices extends Bootstrap
{
	public function Bootstrap()
	{
		/** @var array $serviceConfig */
		$serviceConfig = include DI_CONFIG_PATH . '/Services.php';
		foreach($serviceConfig as $iface => $imp)
		{
			$this->GetIOC()->RegisterClass($imp);
			if(interface_exists($iface))
				$this->GetIOC()->RegisterInterfaceByClass($iface, $imp);
		}
	}
}