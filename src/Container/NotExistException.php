<?php
namespace DIServer\Container;

/**
 * 尝试注册一个不存在的类型
 *
 * @author Back
 */
class NotExistException extends ContainerException
{
	public function __construct($type, $key)
	{
		$msg = "Register type {$type}[{$key}] is not exist.";
		parent::__construct($msg);
	}
}
