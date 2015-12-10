<?php

namespace DIServer;

/**
 * Description of DIIOC
 * 参考了Laravel的IOC容器、MvvmLight的IOC结合自己的理解开发。
 *
 * @author Back
 */
class DIIOC
{

    /**
     * 获取实例
     *
     * @param  string  $abstract
     * @param  array   $parameters
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
	$abstract = $this->getAlias($this->normalize($abstract));

	// If an instance of the type is currently being managed as a singleton we'll
	// just return an existing instance instead of instantiating new instances
	// so the developer can keep using the same objects instance every time.
	if (isset($this->instances[$abstract]))
	{
	    return $this->instances[$abstract];
	}

	$concrete = $this->getConcrete($abstract);

	// We're ready to instantiate an instance of the concrete type registered for
	// the binding. This will instantiate the types, as well as resolve any of
	// its "nested" dependencies recursively until all have gotten resolved.
	if ($this->isBuildable($concrete, $abstract))
	{
	    $object = $this->build($concrete, $parameters);
	}
	else
	{
	    $object = $this->make($concrete, $parameters);
	}

	// If we defined any extenders for this type, we'll need to spin through them
	// and apply them to the object being built. This allows for the extension
	// of services, such as changing configuration or decorating the object.
	foreach ($this->getExtenders($abstract) as $extender)
	{
	    $object = $extender($object, $this);
	}

	// If the requested type is registered as a singleton we'll want to cache off
	// the instances in "memory" so we can return it later without creating an
	// entirely new instance of an object on each subsequent request for it.
	if ($this->isShared($abstract))
	{
	    $this->instances[$abstract] = $object;
	}

	$this->fireResolvingCallbacks($abstract, $object);

	$this->resolved[$abstract] = true;

	return $object;
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
	dump($className);
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
	    //如果构造函数存在
	    //获取这个构造函数的所有参数的依赖项实例
	    $constructorDependences = $this->getFunctionDependencies($constructorMetodReflector);
	    //根据参数的依赖项实例完成实例化
	    return $classReflector->newInstanceArgs($constructorDependences);
	}
	else
	{
	    //构造函数不存在，直接实例化。
	    return $classReflector->newInstanceWithoutConstructor();
	}
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
	$this->buildStack[] = $functionRef; //记录

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
	array_pop($this->buildStack); //销毁记录
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
	$this->buildStack[] = $methodRef; //记录

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
	array_pop($this->buildStack); //销毁记录
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
	    return $this->make($parameter->getClass()->name);
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

}
