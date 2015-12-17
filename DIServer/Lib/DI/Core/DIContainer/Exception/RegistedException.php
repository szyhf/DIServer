<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
