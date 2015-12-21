<?php
namespace DIServer\Lib\DI\DIContainer\Exception;

/**
 * 尝试注册已经已经被注册过的类型且未设置正确的多例时触发
 *
 * @author Back
 */
class RegistedException extends DIContainerException
{
    public function __construct($type,$key)
    {
	$msg = "Register type {$type}[{$key}] has already registed.";
	parent::__construct($msg);
    }
}
