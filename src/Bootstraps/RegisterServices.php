<?php
/**
 * Created by PhpStorm.
 * User: Back
 * Date: 2015/12/26
 * Time: 2:12
 */

namespace DIServer\Bootstraps;

class RegisterServices extends Bootstrap
{
	public function Bootstrap()
	{
		$this->loadService();
		$this->setAlias();
	}

	/**
	 * @throws \DIServer\Container\NotExistException
	 * @throws \DIServer\Container\RegistedException
	 */
	protected function loadService()
	{
		/** @var array $serviceConfig */
		$serviceConfig = include DI_REGISTRY_PATH . '/Server.php';
		foreach($serviceConfig as $iface => $imp)
		{
			$this->getApp()->RegisterClass($imp);
			if(interface_exists($iface))
			{
				$this->getApp()->RegisterInterfaceByClass($iface, $imp);
			}
		}
	}

	protected function setAlias()
	{

	}
}