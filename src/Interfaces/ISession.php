<?php

namespace DIServer\Interfaces;


interface ISession
{
	public function Write($sessionID,$data);
	public function Read($sessionID);
	public function Destory($sessionID);
}