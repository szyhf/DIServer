<?php
namespace DIServer\Lib\DI\DIContainer\Exception;

/**
 * 创建新实例失败
 *
 * @author Back
 */
class MakeFailedException extends DIContainerException
{

    public function __construct($type)
    {
	$msg = "Make instance of {$type} failed.";
	parent::__construct($msg);
    }

}
