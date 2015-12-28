<?php
/**
 * Created by PhpStorm.
 * User: Back
 * Date: 2015/12/28
 * Time: 19:30
 */

namespace DIServer\Interfaces\Container;


interface IContainer extends \ArrayAccess
{
	/**
	 * 清空容器
	 */
	public function Clear();

	/**
	 * 获得指定接口\类型\别名的所有已实例化的实例
	 *
	 * @param string $type 类型或者接口的全称（包括命名空间）
	 *
	 * @return array 所有实例的集合
	 */
	public function GetAllImplementedInstances($type);

	/**
	 * 设置别名（仅保存映射名，不会检查映射名是否存在）
	 *
	 * @param string $alias
	 * @param string $type
	 */
	public function SetAlias($alias, $type);

	/**
	 * 某个类型\接口是否有实例化的实例
	 *
	 * @param string $type 类型\接口全名
	 *
	 * @return bool
	 */
	public function HasImplemented($type);

	/**
	 * 尝试获得指定接口\类型的所有实例
	 *
	 * @param string $type 类或接口的全称
	 *
	 * @return array
	 */
	public function GetAllInstances($type);

	/**
	 * 某个类型\接口是否有被注册过
	 *
	 * @param string $type 类型\接口全名
	 *
	 * @return bool
	 */
	public function HasRegisterer($type);

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
	public function GetInstance($type, $key = null);

	/**
	 * 指定类型的指定Key是否已经被注册
	 *
	 * @param string $type 类型名\接口名
	 * @param string $key  （可选）多例模式下的Key
	 *
	 * @return bool
	 */
	public function IsRegistered($type, $key = null);

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
	public function BuildWithClass($className, array $parameters = []);

	/**
	 * 自动注册
	 *
	 * @param string                       $type
	 * @param string|\Closure|object|array $auto
	 * @param array                        $constructorParams
	 * @param string                       $key
	 */
	public function Register($type, $auto = null, $key = null, array $constructorParams = []);

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
	public function RegisterInterfaceByClass($interface, $class, $key = null, $classKey = null);

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
	public function RegisterInterfaceByFactory($interface, \Closure $factory, array $factoryParams = [], $key = null);

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
	public function RegisterInterfaceByInstance($interface, $instance, $key = null);

	/**
	 * 用工厂方法注册一个类型
	 *
	 * @param string   $class         类型全称
	 * @param \Closure $factory       工厂方法（返回值为实例化结果）
	 * @param array    $factoryParams （可选）工厂方法的自定参数字典
	 * @param string   $key           （可选）多例模式下的key
	 */
	public function RegisterClassByFactory($class, \Closure $factory, array $factoryParams = [], $key = null);

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
	public function RegisterClass($class, array $constructorParams = [], $key = null);

	/**
	 * 直接用实例注册
	 *
	 * @param string $class    类型全称
	 * @param object $instance 工厂方法
	 * @param string $key      （可选）多例模式下的key
	 *
	 * @throws \DIServer\Container\NotTypeOfInstanceException
	 */
	public function RegisterClassByInstance($class, $instance, $key = null);

	/**
	 * 注销
	 *
	 * @param string $type
	 * @param string $key
	 */
	public function Unregister($type, $key = null);

	/**
	 * 移除别名
	 *
	 * @param $alias
	 */
	public function RemoveAlias($alias);
}