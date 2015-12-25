<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer;
/**
 * 给客户端的请求基类
 * @author Back
 */
abstract class Request
{
    protected $Params = '';

    /**
     * 初始化Request
     * @param \swoole_server $server 注入当前进程的$server对象
     */
    public function __construct()
    {
	$className = array_pop(explode('\\', get_called_class()));
	//如果ID被子类设置了，则沿用子类的设置；如果没有，尝试从配置文件中获取;
	$this->ID = is_numeric($this->ID) ? $this->ID : C($className . 'ID');
	//RequestID应该被设置好
	if ($this->ID() === null)
	{
	    DILog(\get_called_class() . '.ID wasn\'t set or configured.', 'w');
	}
	//HandlerID必须是数字
	elseif (!is_numeric($this->ID()))
	{
	    DILog(\get_called_class() . '.ID isn\'t numeric.', 'w');
	}
    }
    
    public function ID()
    {
	return $this->ID;
    }

    public function ToPackage()
    {
	return Package::CreatePackage($this->ID, $this->Params);
    }
}
