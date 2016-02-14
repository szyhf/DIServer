<?php

namespace DIServer\Services;


class Container extends Facade
{
	public static function getFacadeRoot()
	{
		//特殊处理。
		return \DIServer\Container\Container::Instance();
	}

	/**
	 * 清空容器
	 */
	public static function Clear()
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 获得指定接口\类型\别名的所有已实例化的实例
	 *
	 * @param string $type 类型或者接口的全称（包括命名空间）
	 *
	 * @return array 所有实例的集合
	 */
	public static function GetAllImplementedInstances($type)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 设置别名（仅保存映射名，不会检查映射名是否存在）
	 *
	 * @param string $alias
	 * @param string $type
	 */
	public static function SetAlias($alias, $type)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 某个类型\接口是否有实例化的实例
	 *
	 * @param string $type 类型\接口全名
	 *
	 * @return bool
	 */
	public static function HasImplemented($type)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 尝试获得指定接口\类型的所有实例
	 *
	 * @param string $type 类或接口的全称
	 *
	 * @return array
	 */
	public static function GetAllInstances($type)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 某个类型\接口是否有被注册过
	 *
	 * @param string $type 类型\接口全名
	 *
	 * @return bool
	 */
	public static function HasRegistered($type)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 尝试获得指定接口\类型\别名的单例
	 *
	 * @param string $type 类型或者接口的全称（包括命名空间）
	 * @param string $key  （可选）多例模式下的key
	 *
	 * @throws \DIServer\Container\NotRegistedException
	 * @throws \DIServer\Container\DependenceCycleException
	 * @throws \DIServer\Container\MakeFailedException
	 * @return mixed
	 */
	public static function GetInstance($type, $key = null)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 指定类型的指定Key是否已经被注册
	 *
	 * @param string $type 类型名\接口名
	 * @param string $key  （可选）多例模式下的Key
	 *
	 * @return bool
	 */
	static function IsRegistered($type, $key = null)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 根据类名构造一个类的实例
	 * 根据构造函数完成依赖注入
	 * 若构造函数需要使用参数，会优先选用传入的自定义参数数组
	 * 若为未提供自定义参数，会尝试通过type-hint自动从容器实例化
	 * 若type-hint实例化失败，会尝试使用该参数的默认值
	 *
	 * @param string $className  类的名称
	 * @param array  $parameters （可选）构造函数中的自定义参数实例
	 *
	 * @return mixed 类的实例
	 * @throws \DIServer\Container\MakeFailedException
	 */
	public static function BuildWithClass($className, array $parameters = [])
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 自动注册
	 *
	 * @param string                       $type
	 * @param string|\Closure|object|array $auto
	 * @param array                        $constructorParams
	 * @param string                       $key
	 */
	public static function Register($type, $auto = null, $key = null, array $constructorParams = [])
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 注册一个接口的实现类（请另外注册该类）
	 *
	 * @param string $interface 接口全名
	 * @param string $class     实现类全名
	 * @param string $key       （可选）多例模式下interface的key，如果不填则注册为默认实例
	 * @param string $classKey  （可选）实现类是多例时对应的key
	 *
	 * @throws \DIServer\Container\NotExistException
	 * @throws \DIServer\Container\RegistedException
	 */
	public static function RegisterInterfaceByClass($interface, $class, $key = null, $classKey = null)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 注册一个接口的实现工厂
	 *
	 * @param string   $interface     接口全称
	 * @param \Closure $factory       工厂方法（返回值为实例化结果）
	 * @param array    $factoryParams （可选）工厂方法的自定参数字典
	 * @param string   $key           （可选）多例模式下的key
	 *
	 * @throws \DIServer\Container\NotExistException
	 * @throws \DIServer\Container\RegistedException
	 */
	public static function RegisterInterfaceByFactory($interface, \Closure $factory, array $factoryParams = [], $key = null)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 注册一个接口的实例
	 *
	 * @param string $interface 接口全称
	 * @param object $instance  实例
	 * @param string $key       （可选）多例模式下的key
	 *
	 * @throws \DIServer\Container\NotExistException
	 * @throws \DIServer\Container\RegistedException
	 * @throws \DIServer\Container\NotTypeOfInstanceException
	 */
	public static function RegisterInterfaceByInstance($interface, $instance, $key = null)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 用工厂方法注册一个类型
	 *
	 * @param string   $class         类型全称
	 * @param \Closure $factory       工厂方法（返回值为实例化结果）
	 * @param array    $factoryParams （可选）工厂方法的自定参数字典
	 * @param string   $key           （可选）多例模式下的key
	 */
	public static function RegisterClassByFactory($class, \Closure $factory, array $factoryParams = [], $key = null)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 注册一个类型
	 *
	 * @param string $class             类全名（请勿使用抽象类）
	 * @param array  $constructorParams （可选）构造方法的自定参数字典
	 * @param string $key               （可选）多例模式下的key
	 *
	 * @throws \DIServer\Container\NotExistException
	 * @throws \DIServer\Container\RegistedException
	 */
	public static function RegisterClass($class, array $constructorParams = [], $key = null)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 直接用实例注册
	 *
	 * @param string $class    类型全称
	 * @param object $instance 工厂方法
	 * @param string $key      （可选）多例模式下的key
	 *
	 * @throws \DIServer\Container\NotTypeOfInstanceException
	 */
	public static function RegisterClassByInstance($class, $instance, $key = null)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 注销
	 *
	 * @param string $type
	 * @param string $key
	 */
	public static function Unregister($type, $key = null)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 移除别名
	 *
	 * @param $alias
	 */
	public static function RemoveAlias($alias)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 是不是接口或者抽象类
	 *
	 * @param string $abstract
	 *
	 * @return boolean
	 */
	public static function IsAbstract($abstract)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 设置默认实例
	 *
	 * @param \DIServer\Interfaces\IContainer $container
	 */
	public static function SetInstance(IContainer $container)
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}

	/**
	 * 类成员方法的依赖注入调用
	 *
	 * @param \DIServer\Container\object $instance
	 * @param \ReflectionMethod|string   $method     方法的反射实例
	 * @param array                      $parameters （可选）自定义提供的参数-实例列表['$paramName'=>'$instance']
	 *
	 * @return mixed 方法的返回值
	 */
	public static function CallMethod($instance, $method, array $parameters = [])
	{
		return static::__callStatic(__FUNCTION__, func_get_args());
	}
}