<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer;

/**
 * 定义一种检索服务对象的机制，服务对象是为其他对象提供自定义支持的对象。
 * @author Back
 */
interface IServiceProvider
{
    /**
     * 获取指定类型的服务对象。
     * @param type $serviceType 指定要获取的服务对象的类型。
     * @return serviceType 类型的服务对象。如果没有 serviceType 类型的服务对象，则为 null。
     */
    function GetService($serviceType);
}
