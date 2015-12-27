<?php
namespace DIServer\Container;

/**
 * 尝试获取一个未注册的类型时发生
 *
 * @author Back
 */
class NotRegistedException extends ContainerException
{
	public function __construct($type, $key)
	{
		$msg = "Get instance of {$type}[{$key}] is not registed.";
		parent::__construct($msg);
	}
}
