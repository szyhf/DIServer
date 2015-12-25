<?php
namespace DIServer\DI\DIContainer\Exception;

/**
 * 尝试注册一个不存在的类型
 *
 * @author Back
 */
class NotExistException extends DIContainerException
{
    public function __construct($type,$key)
    {
	$msg = "Register type {$type}[{$key}] is not exist.";
	parent::__construct($msg);
    }
}
