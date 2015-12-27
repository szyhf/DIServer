<?php
namespace DIServer\Container;

/**
 * 尝试把一个不属于指定类型的实例注册给该类型时触发。
 *
 * @author Back
 */
class NotTypeOfInstanceException extends ContainerException
{
    public function __construct($type,$key)
    {
	$msg = "Instance registering is not type of {$type}[{$key}].";
	parent::__construct($msg, $code, $previous);
    }
}
