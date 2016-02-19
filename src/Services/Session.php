<?php

namespace DIServer\Services;

use DIServer\Interfaces\ISession;

class Session extends Facade
{
	public static function getFacadeAccessor()
	{
		return ISession::class;
	}

	public static function Get($key, $default = null)
	{
		/** @var ISession $instance */
		$instance = self::getFacadeRoot();

		return $instance->Get($key, $default);
	}

	public static function Set($key, $value)
	{
		/** @var ISession $instance */
		$instance = self::getFacadeRoot();

		return $instance->Set($key, $value);
	}

	public static function Start($sessionID)
	{
		/** @var ISession $instance */
		$instance = self::getFacadeRoot();
		$instance->Reset();
		$instance->Load($sessionID);
	}

	public static function Close()
	{
		/** @var ISession $instance */
		$instance = self::getFacadeRoot();
		$instance->Save();
		$instance->Reset();
	}

	public static function GC()
	{
		/** @var ISession $instance */
		$instance = self::getFacadeRoot();
		$instance->GC();
	}

	public static function Destory()
	{
		/** @var ISession $instance */
		$instance = self::getFacadeRoot();

		return $instance->Destory();
	}
}