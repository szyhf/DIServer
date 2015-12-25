<?php

namespace DIServer;

/**
 * IOC容器接口
 * @author Back
 */
interface IContainer
{
    /**
     * 检查某个类型是否已经被实例化了。
     * @param type $class 抽象
     * @param string $key 自定义键名
     */
    public function ContainsCreated($class, $key = '');

    /**
     * 检查某个抽象是否已经被注册了
     * @param type $class 抽象
     * @param type $key 自定义键名
     */
    public function IsRegistered($class, $key = '');

    /**
     * 注册一个实现了指定接口的指定类型
     * @param type $class 注册的类型
     * @param type $interface 类型实现的接口
     * @param bool $createInstanceImmediately 是否立即实例化
     */
    public function Register($class, $interface = '', bool $createInstanceImmediately = false);

    /**
     * 通过工厂以指定的key注册一个指定类型的实例
     * @param type $class 指定的类型
     * @param \IFactory $factory 构造实例的工厂
     * @param type $key 这个实例的键名
     * @param bool $createInstanceImmediately 是否立即实例化
     */
    public function RegisterByFactory($class, \IFactory $factory, $key = '', bool $createInstanceImmediately = false);

    /**
     * 重置容器
     */
    public function Reset();

    /**
     * 注销一个类型
     * @param type $class 类型
     * @param string $key 该类型的键名
     */
    public function Unregister($classOrInstance, $key = '');
}
