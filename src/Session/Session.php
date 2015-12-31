<?php

namespace DIServer\Session;

use DIServer\Services\Service;

abstract class Session extends Service implements \SessionHandlerInterface
{
	public function Register()
	{
		parent::Register();
		$sessionDriver = $this->getApp()
		                      ->GetInstance(\DIServer\Session\Session::class);
		if(!session_set_save_handler($sessionDriver))
		{
			throw new Exception('error session handler', 11700);
		}
	}
}