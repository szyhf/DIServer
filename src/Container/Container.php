<?php

namespace DIServer\Container;

use DIServer\Interfaces\IContainer;

/**
 * IOC容器类
 */
class Container implements IContainer
{

	/**
	 * 默认容器实例
	 *
	 * @var Container
	 */
	protected static $defaultIOC;

	/**
	 * 默认实例（单例）的键
	 *
	 * @var string
	 */
	protected $defaultKey = '0';

	/**
	 * 类名\抽象类名\接口名->实例的映射
	 * 抽象类名\接口名映射显然不支持自动根据构造函数实例化（=。=）
	 * [$type=>[
	 *        $key=>$instance
	 *        ]
	 * ]
	 *
	 * @var array
	 */
	protected $instances = [];

	/**
	 * 已注册的类名\抽象类名\接口名
	 *
	 * @var array
	 */
	protected $registries = [];

	/**
	 * 类名\抽象类名\接口名->自定义工厂函数的映射
	 * [$type=>[
	 *        $key=>$factory
	 *        ]
	 * ]
	 *
	 * @var array
	 */
	protected $factorys = [];

	/**
	 * 自定义的工厂函数\构造函数参数
	 * [$type=>[
	 *        $key=>$params
	 *        ]
	 * ]
	 *
	 * @var array
	 */
	protected $selfParams = [];

	/**
	 * 抽象名\接口名\类名->它的某个子孙抽象名\某个子孙接口名\某个子孙类名的映射
	 * [$type=>$type]
	 * 子子孙孙无穷匮也
	 *
	 * @var array
	 */
	protected $interfaces = [];

	/**
	 * 构筑堆栈（记录当前正在构筑的类的情况）
	 * [$type,...,$type]
	 *
	 * @var array
	 */
	protected $buildStack = [];

	/**
	 * 已经被创建了实例的类\抽象类\接口
	 * [$type=>[$key=>true|false]]
	 *
	 * @var array
	 */
	protected $implemented = [];

	/**
	 * 全局别名[$alias => $type]
	 *
	 * @var array
	 */
	protected $alias = [];

	protected function __construct()
	{
		$this[__CLASS__] = $this;
	}

	/**
	 * 默认容器实例
	 *
	 * @return Container
	 */
	public static function Instance()
	{
		if(!self::$defaultIOC)
		{
			static::$defaultIOC = new Container();
		}

		return static::$defaultIOC;
	}

	/**
	 * 设置默认实例
	 *
	 * @param \DIServer\Interfaces\IContainer $container
	 */
	public static function SetInstance(IContainer $container)
	{
		static::$defaultIOC = $container;
	}

	/**
	 * 清空容器
	 */
	public function Clear()
	{
		self::$defaultIOC = null;
		unset($this->buildStack);
		unset($this->factorys);
		unset($this->implemented);
		unset($this->instances);
		unset($this->interfaces);
		unset($this->registries);
		unset($this->selfParams);
	}

	/**
	 * 获得指定接口\类型\别名的所有已实例化的实例
	 *
	 * @param string $type 类型或者接口的全称（包括命名空间）
	 *
	 * @return array 所有实例的集合
	 */
	public function GetAllImplementedInstances($type)
	{
		$type = $this->normalizeType($type);
		$type = $this->getAlias($type);

		if($this->HasImplemented($type))
		{
			$instances = $this->instances[$type];
		}
		else
		{//未实例化的情况下返回空集合
			$instances = [];
		}

		return $instances;
	}

	/**
	 * 整理类或者接口的命名
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	protected function normalizeType($type)
	{
		return is_string($type) ? trim($type, '\\') : $type;
	}

	/**
	 * 获取别名对应的类型
	 *
	 * @param string $alias 别名
	 *
	 * @return string
	 */
	protected function getAlias($alias)
	{
		return isset($this->alias[$alias]) ? $this->alias[$alias] : $alias;
	}

	/**
	 * 设置别名（仅保存映射名，不会检查映射名是否存在）
	 *
	 * @param string $alias
	 * @param string $type
	 */
	public function SetAlias($alias, $type)
	{
		$this->alias[$alias] = $type;
	}

	/**
	 * 某个类型\接口是否有实例化的实例
	 *
	 * @param string $type 类型\接口全名
	 *
	 * @return bool
	 */
	public function HasImplemented($type)
	{
		$type = $this->normalizeType($type);
		$type = $this->getAlias($type);

		return isset($this->implemented[$type]);
	}

	/**
	 * 尝试获得指定接口\类型的所有实例
	 *
	 * @param string $type 类或接口的全称
	 *
	 * @return array
	 */
	public function GetAllInstances($type)
	{
		$type = $this->normalizeType($type);
		$type = $this->getAlias($type);

		$instances = [];
		if($this->HasRegistered($type))
		{
			foreach($this->registries[$type] as $key => $value)
			{
				$instances[] = $this->GetInstance($type, $key);
			}
		}

		return $instances;
	}

	/**
	 * 某个类型\接口是否有被注册过
	 *
	 * @param string $type 类型\接口全名
	 *
	 * @return bool
	 */
	public function HasRegistered($type)
	{
		$type = $this->normalizeType($type);
		$type = $this->getAlias($type);

		return isset($this->registries[$type]);
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
	public function GetInstance($type, $key = null)
	{
		$type = $this->normalizeType($type);
		$key = $this->normalizeKey($key);

		if(!$this->IsRegistered($type, $key))
		{
			throw new NotRegistedException($type, $key);
		}

		if($this->IsImplemented($type, $key))
		{
			$instance = $this->instances[$type][$key];
		}
		else
		{
			$parameters = isset($this->selfParams[$type][$key]) ? $this->selfParams[$type][$key] : [];
			$instance = $this->makeInstance($type, $parameters, $key);
			$this->instances[$type][$key] = $instance;
			//将该类型\接口\别名记录为已解决。
			$this->implemented[$type][$key] = true;
		}

		return $instance;
	}

	/**
	 * 准备好key
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	protected function normalizeKey($key = null)
	{
		return $key ?: $this->defaultKey;
	}

	/**
	 * 指定类型的指定Key是否已经被注册
	 *
	 * @param string $type 类型名\接口名
	 * @param string $key  （可选）多例模式下的Key
	 *
	 * @return bool
	 */
	public function IsRegistered($type, $key = null)
	{
		$type = $this->normalizeType($type);
		$type = $this->getAlias($type);
		$key = $this->normalizeKey($key);

		return isset($this->registries[$type][$key]);
	}

	/**
	 * 某个类型的指定Key是否已经被实例化
	 *
	 * @param string $type 类型全称\接口全称\抽象类全称
	 * @param string $key  （可选）多例模式下的key
	 *
	 * @return bool
	 */
	protected function IsImplemented($type, $key = null)
	{
		$type = $this->normalizeType($type);
		$type = $this->getAlias($type);
		$key = $this->normalizeKey($key);

		return isset($this->implemented[$type][$key]);
	}

	/**
	 * 根据类型完成实例化
	 *
	 * @param string $type       类型全称\接口全称\抽象类全称
	 * @param array  $parameters （可选）自定义实例化参数
	 * @param string $key        (可选）多例模式下的key
	 *
	 * @return mixed
	 * @throws \DIServer\Container\DependenceCycleException
	 * @throws \DIServer\Container\MakeFailedException
	 */
	protected function MakeInstance($type, array $parameters = [], $key = null)
	{
		$type = $this->normalizeType($type);
		$type = $this->getAlias($type);

		if(in_array($type . '[' . $key . ']', $this->buildStack))
		{
			throw new DependenceCycleException($this->buildStack);
		}
		$this->buildStack[] = $type . '[' . $key . ']';
		//实时构造
		if(isset($this->interfaces[$type][$key]))
		{
			//检查指定映射([$class=>$classKey]
			$target = $this->interfaces[$type][$key];

			list($class, $classKey) = each($target);
			//递归直到实现
			$instance = $this->GetInstance($class, $classKey);
		}
		elseif(isset($this->factorys[$type][$key]))
		{
			//尝试使用工厂方法生成
			$closureFactory = $this->factorys[$type][$key];
			$instance = $this->buildWithClosure($closureFactory, $parameters);
		}
		elseif(class_exists($type))
		{
			//尝试使用构造函数生成
			$instance = $this->buildWithClass($type, $parameters);
		}
		else
		{
			throw new MakeFailedException($type);
		}
		array_pop($this->buildStack);

		return $instance;
	}

	/**
	 * 通过匿名工厂函数构造一个对象实例
	 * 若工厂函数需要使用参数，会优先选用传入的自定义参数数组
	 * 若为未提供自定义参数，会尝试通过type-hint自动从容器实例化
	 * 若type-hint实例化失败，会尝试使用该参数的默认值
	 *
	 * @param \Closure $closure    匿名工厂函数
	 * @param array    $parameters （可选）工厂函数的自定义参数['$paramName'=>'$instance']
	 *
	 * @return mixed 构造的实例
	 */
	protected function buildWithClosure(\Closure $closure, array $parameters = [])
	{
		/**
		 * 如果传入了一个匿名函数，那么我们直接认为这是一个完整的工厂函数
		 * 直接调用这个函数，并返回执行的结果（不绑定）
		 */
		//$funcRef = new \ReflectionFunction($closure);

		return call_user_func_array($closure, $parameters);
	}

	/**
	 * 函数方法的依赖注入调用
	 * 若函数需要使用参数，会优先选用传入的自定义参数数组
	 * 若为未提供自定义参数，会尝试通过type-hint自动从容器实例化
	 * 若实例化失败，会尝试使用该参数的默认值
	 *
	 * @param \ReflectionFunction $functionRef 方法的反射实例
	 * @param array               $parameters  （可选）自定义提供的参数-实例列表['$paramName'=>'$instance']
	 *
	 * @return mixed 方法的返回值
	 */
	protected function callFunction(\ReflectionFunction $functionRef, array $parameters = [])
	{
		$res = null;

		$dependencies = $functionRef->getParameters();
		if(empty($dependencies))
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
	 * 获取一个函数方法或者成员方法的依赖项参数实例集合
	 * 若方法需要使用参数，会优先选用传入的自定义参数数组
	 * 若为未提供自定义参数，会尝试通过type-hint自动从容器实例化
	 * 若实例化失败，会尝试使用该参数的默认值
	 *
	 * @param \ReflectionFunctionAbstract $abstractFunctionReflector
	 * @param array                       $parameters （可选）自定义提供的参数-实例列表['$paramName'=>'$instance']
	 *
	 * @return array
	 */
	protected function getFunctionDependencies(\ReflectionFunctionAbstract $abstractFunctionReflector, array $parameters = [])
	{
		$dependencies = $abstractFunctionReflector->getParameters();
		if(empty($dependencies))
		{
			return [];
		}
		else
		{
			//整合依赖项与自定义参数
			$parameters = $this->keyParametersByArgument($dependencies, $parameters);

			//构造整合依赖项实例（包括已经由用户提供的）
			$instances = $this->getDependencies($dependencies, $parameters);

			return $instances;
		}
	}

	/**
	 * 构建['$dependenciesName'=>'$selfDefinedInstance']的映射关系
	 *
	 * @param array $dependencies 必须的依赖参数列表[\ReflectionParameter]
	 * @param array $parameters   自定义提供的参数-实例列表['$paramName'=>'$instance']
	 *
	 * @return array ['dependenceName'=>'$instance']
	 */
	protected function keyParametersByArgument(array $dependencies, array $parameters)
	{
		foreach($parameters as $key => $value)
		{
			if(is_numeric($key))
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
	 * @param  array $parameters 参数反射对象集合[\ReflectionParameter]
	 * @param  array $primitives （可选）自己提供的参数实例集合['$paramName'=>'$instance']
	 *
	 * @return array ['$paramInstance'] 参数对应的实例集合
	 */
	protected function getDependencies(array $parameters, array $primitives = [])
	{
		$dependencies = [];

		foreach($parameters as $parameter)
		{
			/** @var \ReflectionParameter $parameter */
			$dependency = $parameter->getClass();
			if(array_key_exists($parameter->name, $primitives))
			{
				//由自定义参数实例提供
				$dependencies[] = $primitives[$parameter->name];
			}
			elseif(is_null($dependency))
			{
				//如果class是null，说明可能是标量类型
				$dependencies[] = $this->resolveNonClassParameter($parameter);
			}
			else
			{
				//如果class不是null，尝试依靠容器机制完成实例化
				$dependencies[] = $this->resolveClassParameter($parameter);
			}
		}

		return (array)$dependencies;
	}

	/**
	 * 处理不是对象类型的参数（如标量类型）
	 *
	 * @param  \ReflectionParameter $parameter
	 *
	 * @return mixed 参数的默认取值
	 * @throws \DIServer\Container\UnresolvableParameterException 无法获得
	 */
	protected function resolveNonClassParameter(\ReflectionParameter $parameter)
	{
		if($parameter->isDefaultValueAvailable())
		{
			return $parameter->getDefaultValue();
		}
		else
		{
			throw new UnresolvableParameterException($parameter);
		}
	}

	/**
	 * 尝试根据参数类型从容器中找到实例
	 *
	 * @param  \ReflectionParameter $parameter 参数的反射对象
	 *
	 * @throws \DIServer\Container\UnresolvableParameterException 无法获得
	 * @return mixed
	 */
	protected function resolveClassParameter(\ReflectionParameter $parameter)
	{
		try
		{
			return $this->GetInstance($parameter->getClass()->name);
		}
		catch(ContainerException $ex)
		{
			//先尝试直接从容器获取对应实例
			//如果没有再考虑该参数的默认值
			//要不就挂了
			if($parameter->isOptional())
			{
				return $parameter->getDefaultValue();
			}
			throw new UnresolvableParameterException($parameter);
		}
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
	public function BuildWithClass($className, array $parameters = [])
	{
		//构造类反射对象
		$classReflector = new \ReflectionClass($className);

		//如果是抽象类或者接口，则无法实例化（异常）
		if(!$classReflector->isInstantiable())
		{
			throw new MakeFailedException($className);
		}

		//获取类的构造函数的方法反射类
		$constructorMetodReflector = $classReflector->getConstructor();

		if($constructorMetodReflector)
		{
			//如果构造函数存在，获取这个构造函数的所有参数的依赖项实例并实例化
			$constructorDependences = $this->getFunctionDependencies($constructorMetodReflector, $parameters);

			//根据参数的依赖项实例完成实例化
			$object = $classReflector->newInstanceArgs($constructorDependences);
		}
		else
		{
			//构造函数不存在，直接实例化。
			$object = $classReflector->newInstanceWithoutConstructor();
		}

		return $object;
	}

	/**
	 * Determine if a given offset exists.
	 *
	 * @param  string $key
	 *
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return $this->IsRegistered($key);
	}

	/**
	 * Get the value at a given offset.
	 *
	 * @param  string $key
	 *
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->GetInstance($key);
	}

	/**
	 * Set the value at a given offset.
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 *
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		// If the value is not a Closure, we will make it one. This simply gives
		// more "drop-in" replacement functionality for the Pimple which this
		// container's simplest functions are base modeled and built after.
		if(!$value instanceof \Closure)
		{
			$value = function () use ($value)
			{
				return $value;
			};
		}

		$this->Register($key, $value);
	}

	/**
	 * 自动注册
	 *
	 * @param string                       $type
	 * @param string|\Closure|object|array $auto
	 * @param array                        $constructorParams
	 * @param string                       $key
	 */
	public function Register($type, $auto = null, $key = null, array $constructorParams = [])
	{
		if($this->isAbstract($type))
		{
			//接口相关
			if(is_string($auto))
			{
				//把类型注册给接口
				$this->RegisterInterfaceByClass($type, $auto, $key);
			}
			elseif(is_array($auto))
			{
				//把指定多例模式下的指定实例注册给接口
				//要求$auto = ['$class'=>'$key']
				list($class, $classKey) = each($auto);
				$this->RegisterInterfaceByClass($type, $class, $key, $classKey);
			}
			elseif($auto instanceof \Closure)
			{
				//提供了工厂方法
				$this->RegisterInterfaceByFactory($key, $auto, $constructorParams, $key);
			}
			else
			{
				//当作提供的实例
				$this->RegisterInterfaceByInstance($type, $auto, $key);
			}
		}
		elseif(class_exists($type))
		{
			//类相关
			if($auto instanceof \Closure)
			{
				//提供了工厂方法
				$this->RegisterClassByFactory($type, $auto, $constructorParams, $key);
			}
			elseif(isset($auto))
			{
				//当作提供提供了实例
				$this->RegisterClassByInstance($type, $auto, $key);
			}
			else
			{
				//使用默认构造函数
				$this->RegisterClass($type, $constructorParams, $key);
			}
		}
	}

	/**
	 * 是不是接口或者抽象类
	 *
	 * @param string $abstract
	 *
	 * @return boolean
	 */
	public function isAbstract($abstract)
	{
		if(!interface_exists($abstract))
		{
			//如果是抽象类也可以接受
			try{
				$refClass = new \ReflectionClass($abstract);
				return $refClass->isAbstract();
			}catch (\ReflectionException $ex){

			}
		}
		else
		{
			return true;
		}
		return false;
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
	public function RegisterInterfaceByClass($interface, $class, $key = null, $classKey = null)
	{
		if(!$this->isAbstract($interface))
		{
			throw new NotExistException($interface, $key);
		}
		elseif($this->IsRegistered($interface, $key))
		{
			throw new RegistedException($interface, $key);
		}
		else
		{
			$key = $this->normalizeKey($key);
			$classKey = $this->normalizeKey($classKey);

			$interface = $this->normalizeType($interface);
			$class = $this->normalizeType($class);

			$this->registries[$interface][$key] = true;
			$this->interfaces[$interface][$key] = [$class => $classKey];
		}
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
	public function RegisterInterfaceByFactory($interface, \Closure $factory, array $factoryParams = [], $key = null)
	{
		if(!$this->isAbstract($interface))
		{
			throw new NotExistException($interface, $key);
		}
		elseif($this->IsRegistered($interface, $key))
		{
			throw new RegistedException($interface, $key);
		}
		else
		{
			$key = $this->normalizeKey($key);
			$interface = $this->normalizeType($interface);

			$this->registries[$interface][$key] = true;
			$this->factorys[$interface][$key] = $factory;
			$this->registerSelfParams($interface, $factoryParams, $key);
		}
	}

	/**
	 * 记录工厂函数\构造函数的自定义参数（如果有）
	 *
	 * @param string $class 类全名
	 * @param array  $params
	 * @param string $key
	 */
	protected function registerSelfParams($class, array $params = [], $key = null)
	{
		if(count($params))
		{
			$key = $this->normalizeKey($key);
			//如果自定义数组非空
			$this->selfParams[$class][$key] = $params;
		}
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
	public function RegisterInterfaceByInstance($interface, $instance, $key = null)
	{
		if(!$this->isAbstract($interface))
		{
			throw new NotExistException($interface, $key);
		}
		elseif($this->IsRegistered($interface, $key))
		{
			throw new RegistedException($interface, $key);
		}

		if(!($instance instanceof $interface))
		{
			throw new NotTypeOfInstanceException($interface, $key);
		}
		else
		{
			$key = $this->normalizeKey($key);
			$interface = $this->normalizeType($interface);

			$this->registries[$interface][$key] = true;
			$this->instances[$interface][$key] = $instance;
			$this->implemented[$interface][$key] = true;
		}
	}

	/**
	 * 用工厂方法注册一个类型
	 *
	 * @param string   $class         类型全称
	 * @param \Closure $factory       工厂方法（返回值为实例化结果）
	 * @param array    $factoryParams （可选）工厂方法的自定参数字典
	 * @param string   $key           （可选）多例模式下的key
	 */
	public function RegisterClassByFactory($class, \Closure $factory, array $factoryParams = [], $key = null)
	{
		$key = $this->normalizeKey($key);
		$class = $this->normalizeType($class);

		$this->RegisterClass($class, $factoryParams, $key);
		$this->factorys[$class][$key] = $factory;
		$this->registerSelfParams($class, $factoryParams, $key);
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
	public function RegisterClass($class, array $constructorParams = [], $key = null)
	{
		$class = $this->normalizeType($class);
		if(!class_exists($class))
		{
			throw new NotExistException($class, $key);
		}
		elseif($this->IsRegistered($class, $key))
		{
			throw new RegistedException($class, $key);
		}
		else
		{
			$key = $this->normalizeKey($key);
			$this->registries[$class][$key] = true;
			$this->registerSelfParams($class, $constructorParams, $key);
		}
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
	public function RegisterClassByInstance($class, $instance, $key = null)
	{
		$class = $this->normalizeType($class);
		$key = $this->normalizeKey($key);

		$this->RegisterClass($class, [], $key);

		if(is_a($instance, $class))
		{
			$key = $this->normalizeKey($key);
			$this->instances[$class][$key] = $instance;
			$this->implemented[$class][$key] = true;
		}
		else
		{
			throw new NotTypeOfInstanceException($class, $key);
		}
	}

	/**
	 * Unset the value at a given offset.
	 *
	 * @param  string $key
	 *
	 * @return void
	 */
	public function offsetUnset($key)
	{
		$this->Unregister($key);
	}

	/**
	 * 注销
	 *
	 * @param string $type
	 * @param string $key
	 */
	public function Unregister($type, $key = null)
	{
		$alias = $type;
		$type = $this->getAlias($type);
		$this->RemoveAlias($alias);
		$type = $this->normalizeType($type);
		$key = $this->normalizeKey($key);

		$this->unregisterTypeByKey($this->factorys, $type, $key);
		$this->unregisterTypeByKey($this->implemented, $type, $key);
		$this->unregisterTypeByKey($this->instances, $type, $key);
		$this->unregisterTypeByKey($this->interfaces, $type, $key);
		$this->unregisterTypeByKey($this->registries, $type, $key);
		$this->unregisterTypeByKey($this->selfParams, $type, $key);


		//unset($this->implemented[$type][$key]);
		//if(isset($this->implemented[$type]))
		//{
		//	if(!count($this->implemented[$type]))
		//	{
		//		unset($this->implemented[$type]);
		//	}
		//}
		//
		//unset($this->instances[$type][$key]);
		//if(isset($this->instances[$type]))
		//{
		//	if(!count($this->instances[$type]))
		//	{
		//		unset($this->instances[$type]);
		//	}
		//}
		//
		//unset($this->interfaces[$type][$key]);
		//
		//if(!count($this->interfaces[$type]))
		//{
		//	unset($this->interfaces[$type]);
		//}
		//
		//unset($this->registries[$type][$key]);
		//if(!count($this->registries[$type]))
		//{
		//	unset($this->registries[$type]);
		//}
		//
		//unset($this->selfParams[$type][$key]);
		//if(!count($this->selfParams[$type]))
		//{
		//	unset($this->selfParams[$type]);
		//}
	}

	protected function unregisterTypeByKey(&$ary, $type, $key)
	{
		unset($ary[$type][$key]);
		if(isset($ary[$type]))
		{
			if(!count($ary[$type]))
			{
				unset($ary[$type]);
			}
		}
	}

	/**
	 * 移除别名
	 *
	 * @param $alias
	 */
	public function RemoveAlias($alias)
	{
		unset($this->alias[$alias]);
	}

	/**
	 * 类成员方法的依赖注入调用
	 *
	 * @param object                   $instance
	 * @param \ReflectionMethod|string $method     方法的反射实例
	 * @param array                    $parameters （可选）自定义提供的参数-实例列表['$paramName'=>'$instance']
	 *
	 * @return mixed 方法的返回值
	 */
	public function CallMethod($instance, $method, array $parameters = [])
	{
		$res = null;
		if(is_string($method))
		{
			if(method_exists($instance, $method))
			{
				$methodRef = new \ReflectionMethod(get_class($instance), $method);
			}
			else
			{
				throw new \Exception("Calling method [$method] of instance [$instance] is not exist.");
			}
		}
		elseif($method instanceof \ReflectionMethod)
		{
			$methodRef = $method;
		}
		else
		{
			throw new \Exception("Parameter \$method must be a string or instance of \\ReflectionMethod.");
		}
		$dependencies = $methodRef->getParameters();
		if(empty($dependencies))
		{
			//没有参数，直接调用
			$res = $methodRef->invoke($instance);
		}
		else
		{
			//构造整合依赖项实例（包括已经由用户提供的）
			$instances = $this->getDependencies($dependencies, $parameters);

			$res = $methodRef->invokeArgs($instance, $instances);
		}

		return $res;
	}

	/**
	 * 工厂函数是否可以用于实例化该类型
	 *
	 * @param \Closure $factory
	 * @param          $type
	 *
	 * @return bool
	 */
	protected function isBuildable(\Closure $factory, $type)
	{
		return $factory === $type || $factory instanceof \Closure;
	}

}
