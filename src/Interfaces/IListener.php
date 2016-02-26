<?php

namespace DIServer\Interfaces;


interface IListener
{
	public function GetSetting();

	public function GetHost();

	public function GetPort();

	public function GetType();
}