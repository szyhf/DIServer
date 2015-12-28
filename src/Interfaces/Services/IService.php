<?php
namespace DIServer\Interfaces\Services;
use DIServer\Interfaces\IApplication as IApplication;

interface IService
{
	/**
	 * IService constructor.
	 *
	 * @param \DIServer\Interfaces\IApplication $app
	 */
	public function __construct(IApplication $app);

	/**
	 * @return mixed
	 */
	public function Register();
}