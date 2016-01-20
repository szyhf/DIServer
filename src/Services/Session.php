<?php

namespace DIServer\Services;

use DIServer\Interfaces\ISession;

class Session extends Facade
{
	public static function getFacadeAccessor()
	{
		return ISession::class;
	}

	public static function Start($sessionID)
	{
		/** @var ISession $instance */
		$instance = self::getFacadeRoot();

		return $instance->Read($sessionID);
	}

	public static function Close($sessionID)
	{
		/** @var ISession $instance */
		$instance = self::getFacadeRoot();
		$instance->Write($sessionID, self::$session);
		self::$session = null;
	}
}