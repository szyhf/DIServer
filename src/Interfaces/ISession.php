<?php

namespace DIServer\Interfaces;


interface ISession extends IStorage
{
	public function Save();

	public function Load($sessionID);

	public function Destory();

	public function Reset();

	public function GC();
}