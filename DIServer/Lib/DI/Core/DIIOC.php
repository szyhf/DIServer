<?php

namespace DIServer;

/**
 * IOC工具
 * 参考了Laravel的IOC容器、MvvmLight的IOC结合自己的理解开发。
 * 异常还没整理
 * 命名还没规范化（调试环境有些函数不方便设置为public）
 * @author Back
 */
class DIIOC
{
    protected $instances = [];
    protected $aliases = [];
    protected $factorys = [];
    protected $bindings = [];
    protected $buildStack = [];
    protected $resolved = [];
    protected $keyInstances = [];

    /**
     * 尝试获得指定接口\类型\别名的实例
     * @param string $type 类型或者接口的全称（包括命名空间）|别名
     * @param array $parameters
     * @return mixed
     */
    protected function GetInstance(string $type, string $key = NULL, array $parameters = [])
    {
	$type = $this->GetTypeByAlias($this->normalize($type));

	if (empty($key))
	{
	    //直接尝试从实例化后的列表获取全局唯一实例
	    if (isset($this->instances[$type]))
	    {
		$instance = $this->instances[$type];
	    }
	    else
	    {
		$instance = $this->makeInstance($type, $parameters);
	    }
	}
	else
	{
	    throw new Exception('not support multi-instance now.');
	}

	$this->instances[$type] = $instance;
	//将该类型\接口\别名记录为已解决。
	$this->resolved[$type] = true;

	return $instance;
    }

    /**
     * 根据类型完成实例化
     * @param string $type 类型全称\接口全称\抽象类全称
     * @param array $parameters （可选）构造函数的参数
     */
    protected function makeInstance(string $type, array $parameters = [])
    {
	$instance = NULL;

	//实时构造
	if (isset($this->bindings[$type]))
	{
	    //检查指定映射（如接口->...->类，抽象类->...->类，类->...->子类）
	    $target = $this->bindings[$type];
	    //递归直到实现
	    $instance = $this->GetInstance($target,null, $parameters);
	}
	elseif (isset($this->factorys[$type]))
	{
	    //尝试使用工厂方法生成
	    $closureFactory = $this->factorys[$type];
	    $instance = $this->buildWithClosure($closureFactory, $parameters);
	}
	elseif (class_exists($type))
	{
	    //尝试使用构造函数生成
	    $instance = $this->buildWithClass($type, $parameters);
	}
	else
	{
	    throw new Exception('makeInstance failed.');
	}
	return $instance;
    }

    /**
     * 完成多实例模式的实例化
     * @param string $type 类型全称\接口全称\抽象类全称
     * @param string $key 实例别名
     * @param array $parameters
     */
    protected function makeInstanceByKey(string $type, string $key, array $parameters = [])
    {
	
    }

    /**
     * 根据别名获取原名
     * @param string $alias 别名
     * @return string
     */
    protected function GetTypeByAlias(string $alias)
    {
	return isset($this->aliases[$alias]) ? $this->aliases[$alias] : $alias;
    }

    /**
     * 某个类型是否已经被实例化
     * @param string $type 类型全称\接口全称\抽象类全称
     * @return bool
     */
    protected function IsImplemented(string $type)
    {
	return (bool)$this->resolved[$type];
    }
    
    /**
     * 
     * @param type $type
     * @return mixed
     */
    protected function normalize($type)
    {
	return is_string($type) ? trim($type, '\\') : $type;
    }

    /**
     * 通过匿名工厂函数构造一个对象实例
     * 若工厂函数需要使用参数，会优先选用传入的自定义参数数组
     * 若为未提供自定义参数，会尝试通过type-hint自动从容器实例化
     * 若type-hint实例化失败，会尝试使用该参数的默认值
     * 
     * @param \Closure $closure 匿名工厂函数
     * @param array $parameters （可选）工厂函数的自定义参数['$paramName'=>'$instance']
     * @return mixed 构造的实例
     * 
     * @throws \DIServer\Exception 实例化失败
     */
    protected function buildWithClosure(\Closure $closure, array $parameters = [])
    {
	/**
	 * 如果传入了一个匿名函数，那么我们直接认为这是一个完整的工厂函数
	 * 直接调用这个函数，并返回执行的结果（不绑定）
	 */
	$funcRef = new \ReflectionFunction($closure);
	return $this->callFunction($funcRef, $parameters);
    }

    /**
     * 根据类名构造一个类的实例
     * 根据构造函数完成依赖注入
     * 若构造函数需要使用参数，会优先选用传入的自定义参数数组
     * 若为未提供自定义参数，会尝试通过type-hint自动从容器实例化
     * 若type-hint实例化失败，会尝试使用该参数的默认值
     * 
     * @param string $className 类的名称
     * @param array $parameters （可选）构造函数中的自定义参数实例
     * @return mixed 类的实例
     * 
     * @throws Exception
     */
    protected function buildWithClass(string $className, array $parameters = [])
    {
	$this->bulidStack[] = $className;
	//构造类反射对象
	$classReflector = new \ReflectionClass($className);

	//如果是抽象类或者接口，则无法实例化（异常）
	if (!$classReflector->isInstantiable())
	{
	    $message = "Target [$concrete] is not instantiable.";
	    return $message;
	}

	//获取类的构造函数的方法反射类	 
	$constructorMetodReflector = $classReflector->getConstructor();

	if ($constructorMetodReflector)
	{
	    //如果构造函数存在，获取这个构造函数的所有参数的依赖项实例并实例化
	    $constructorDependences = $this->getFunctionDependencies($constructorMetodReflector);

	    //根据参数的依赖项实例完成实例化
	    $object = $classReflector->newInstanceArgs($constructorDependences);
	}
	else
	{
	    //构造函数不存在，直接实例化。
	    $object = $classReflector->newInstanceWithoutConstructor();
	}
	array_pop($this->bulidStack[]);
	return $object;
    }

    /**
     * 函数方法的依赖注入调用
     * 若函数需要使用参数，会优先选用传入的自定义参数数组
     * 若为未提供自定义参数，会尝试通过type-hint自动从容器实例化
     * 若实例化失败，会尝试使用该参数的默认值
     * 
     * @param \ReflectionFunction $functionRef 方法的反射实例
     * @param array $parameters （可选）自定义提供的参数-实例列表['$paramName'=>'$instance']
     * @return mixed 方法的返回值
     */
    protected function callFunction(\ReflectionFunction $functionRef, array $parameters = [])
    {
	$res = null;

	$dependencies = $functionRef->getParameters();
	if (empty($dependencies))
	{
	    //没有参数，直接调用
	    $res = $functionRef->invoke();
	}
	else
	{
	    //构造依赖项实例（包括已经由用户提供的）
	    $instances = $this->getFunctionDependencies($functionRef, $parameters);

	    $res = $functionRef->invokeArgs($instances);
	}

	return $res;
    }

    /**
     * 类成员方法的依赖注入调用
     * 
     * @param \ReflectionMethod $methodRef 方法的反射实例
     * @param array $parameters （可选）自定义提供的参数-实例列表['$paramName'=>'$instance']
     * @return mixed 方法的返回值
     */
    protected function callMethod(object $instance, \ReflectionMethod $methodRef, array $parameters = [])
    {
	$res = null;

	$dependencies = $methodRef->getParameters();
	if (empty($dependencies))
	{
	    //没有参数，直接调用
	    $res = $methodRef->invoke();
	}
	else
	{
	    //构造整合依赖项实例（包括已经由用户提供的）
	    $instances = $this->getFunctionDependencies(
		    $dependencies, $parameters
	    );

	    $res = $methodRef->invokeArgs($instance, $instances);
	}
	return $res;
    }

    /**
     * 获取一个函数方法或者成员方法的依赖项参数实例集合
     * 若方法需要使用参数，会优先选用传入的自定义参数数组
     * 若为未提供自定义参数，会尝试通过type-hint自动从容器实例化
     * 若实例化失败，会尝试使用该参数的默认值
     * 
     * @param \ReflectionMethod $abstractFunctionReflector
     * @param array $parameters （可选）自定义提供的参数-实例列表['$paramName'=>'$instance']
     */
    protected function getFunctionDependencies(\ReflectionFunctionAbstract $abstractFunctionReflector, array $parameters = [])
    {
	$dependencies = $abstractFunctionReflector->getParameters();
	if (empty($dependencies))
	{
	    return [];
	}
	else
	{
	    //整合依赖项与自定义参数
	    $parameters = $this->keyParametersByArgument(
		    $dependencies, $parameters
	    );

	    //构造整合依赖项实例（包括已经由用户提供的）
	    $instances = $this->getDependencies(
		    $dependencies, $parameters
	    );

	    return $instances;
	}
    }

    /**
     * 构建['$dependenciesName'=>'$selfDefinedInstance']的映射关系
     * 
     * @param array $dependencies 必须的依赖参数列表[\ReflectionParameter]
     * @param array $parameters 自定义提供的参数-实例列表['$paramName'=>'$instance']
     * @return array ['dependenceName'=>'$instance']
     */
    protected function keyParametersByArgument(array $dependencies, array $parameters)
    {
	foreach ($parameters as $key => $value)
	{
	    if (is_numeric($key))
	    {
		unset($parameters[$key]); //去掉不合法的参数名
		$parameters[$dependencies[$key]->name] = $value;
	    }
	}

	return $parameters;
    }

    /**
     * 根据参数反射，将依赖项全部实例化
     *
     * @param  array  $parameters 参数反射对象集合[\ReflectionParameter]
     * @param  array  $primitives （可选）自己提供的参数实例集合['$paramName'=>'$instance']
     * @return array ['$paramInstance'] 参数对应的实例集合
     */
    protected function getDependencies(array $parameters, array $primitives = [])
    {
	$dependencies = [];

	foreach ($parameters as $parameter)
	{
	    $dependency = $parameter->getClass();
	    if (array_key_exists($parameter->name, $primitives))
	    {
		//由自定义参数实例提供
		$dependencies[] = $primitives[$parameter->name];
	    }
	    elseif (is_null($dependency))
	    {
		//如果class是null，说明可能是标量类型
		$dependencies[] = $this->resolveNonClass($parameter);
	    }
	    else
	    {
		//如果class不是null，尝试依靠容器机制完成实例化
		$dependencies[] = $this->resolveClass($parameter);
	    }
	}

	return (array) $dependencies;
    }

    /**
     * 处理不是对象类型的参数（如标量类型）
     *
     * @param  \ReflectionParameter  $parameter 
     * @return mixed 参数的默认取值
     * @throws Exception 无法获得
     */
    protected function resolveNonClass(\ReflectionParameter $parameter)
    {
	if ($parameter->isDefaultValueAvailable())
	{
	    return $parameter->getDefaultValue();
	}
	else
	{
	    $message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";
	    throw new Exception($message);
	}
    }

    /**
     * 尝试根据参数类型从容器中找到实例
     *
     * @param  \ReflectionParameter  $parameter 参数的反射对象
     * @return mixed
     */
    protected function resolveClass(\ReflectionParameter $parameter)
    {
	try
	{
	    return $this->GetInstance($parameter->getClass()->name);
	}
	catch (\Exception $e)
	{
	    //先尝试直接从容器获取对应实例
	    //如果没有再考虑该参数的默认值
	    //要不就挂了
	    if ($parameter->isOptional())
	    {
		return $parameter->getDefaultValue();
	    }
	    throw $e;
	}
    }

    /**
     * 工厂函数是否可以用于实例化该类型
     *
     * @param  mixed   $concrete
     * @param  string  $abstract
     * @return bool
     */
    protected function isBuildable(\Closure $factory, $type)
    {
	return $concrete === $type || $concrete instanceof \Closure;
    }
}
