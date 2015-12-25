<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer\DI\Locater;

/**
 * Handler的专用管理器
 *
 * @author Back
 */
class HandlerLocater
{
    protected $_handlers = [];
    protected $_handlersMap =[];
    
    public function __construct(\DIServer\HandlersFactory $handlerFactory)
    {
	if($handlerFactory)
	{
	    $tempHandlers = $handlerFactory->Factory();
	    /**
	     * @var \DIServer\Handler $handler 
	     */
	    foreach ($tempHandlers as $handler)
	    {		
		
	    }
	}
	echo "Handler Locater\n";
    }
    
    /**
     * 注册一个Handler。
     * @param \DIServer\Handler $handler
     */
    public function Register(\DIServer\Handler &$handler)
    {
	
    }
    
    /**
     * 根据HandlerID获取Handler对象数组
     * @param int $handlerID
     * @return array 所有ID为指定ID的Handler数组
     */
    public function GetAllInstances($handlerID)
    {
	
    }
    
    /**
     * 根据Handler的类名获取Handler对象
     * @param string $HandlerName Handler的名字
     * @return \DIServer\Handler Handler对象
     */
    public function GetInstance($handlerName)
    {
	
    }
    
    /**
     * 当前总共有多少Handler可用。
     * @return int 注册Handler的数量
     */
    public function Count()
    {
	return count($_handlersMap);
    }
}
