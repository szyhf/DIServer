<?php

namespace DIServer;

/**
 * 通用的服务定位器对象
 * @author Back
 */
interface IServiceLocator extends IServiceProvider
{

    /**
     * 获取当前注册的指定服务的所有实例
     * @param type $service 服务类型
     * @return mixed 服务实例
     */
    function GetAllInstances($service);

    /**
     * 获得指定键名的指定服务类型的实例
     * @param type $service 服务类型
     * @param type $key 键名
     * @return mixed 对象实例
     */
    function GetInstance($service, $key = '');
}
