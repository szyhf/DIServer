<?php

namespace DIServer\Interfaces;


interface IBootstrap
{
	public function BeforeBootstrap();

	public function Bootstrap();

	public function AfterBootstrap();
}