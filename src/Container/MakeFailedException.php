<?php
namespace DIServer\Container;

/**
 * 创建新实例失败
 *
 * @author Back
 */
class MakeFailedException extends ContainerException
{

    public function __construct($type)
    {
	$msg = "Make instance of {$type} failed.";
	parent::__construct($msg);
    }

}
