<?php

namespace DIServer\Pipeline;

//refference to Lavarel/Pipleline
class Base
{
	protected $passable;
	protected $method = 'Handle';
	protected $pipes = [];

	/**
	 * 连贯方法：设置传入对象
	 *
	 * @param $passable
	 *
	 * @return $this
	 */
	public function Send($passable)
	{
		$this->passable = $passable;

		return $this;
	}

	/**
	 * 连贯方法：设置管道的集合
	 *
	 * @param $pipes
	 *
	 * @return $this
	 */
	public function Through($pipes)
	{
		$this->pipes = is_array($pipes) ? $pipes : func_get_args();

		return $this;
	}

	/**
	 * $pipe中实现了function($passable,$next)方法的名称
	 *
	 * @param $method
	 *
	 * @return $this
	 */
	public function Via($method)
	{
		$this->method = $method;

		return $this;
	}


	/**
	 * 通过连贯操作完成调用
	 *
	 * @param \Closure $destination
	 *
	 * @return mixed
	 */
	public function Then(\Closure $destination)
	{
		$pipeClosure = $this->Prepared($destination);

		return call_user_func($pipeClosure, $this->passable);
	}


	/**
	 * 获取构造完成的管道的匿名方法
	 *
	 * @param \Closure $destination
	 *
	 * @return \Closure
	 */
	public function Prepared(\Closure $destination)
	{
		$firstSlice = $this->getInitialSlice($destination);

		$pipes = array_reverse($this->pipes);

		return array_reduce($pipes, $this->getSlice(), $firstSlice);
	}

	/**
	 * 获取通道中的一段的代理方法
	 *
	 * @return \Closure
	 */
	protected function getSlice()
	{
		return function ($stack, $pipe)
		{
			return function ($passable) use ($stack, $pipe)
			{
				return call_user_func([$pipe, $this->method], $passable, $stack);
			};
		};
	}

	/**
	 * 构造初始化的代理匿名函数
	 *
	 * @param \Closure $destination
	 *
	 * @return \Closure
	 */
	protected function getInitialSlice(\Closure $destination)
	{
		return function ($passable) use ($destination)
		{
			return call_user_func($destination, $passable);
		};
	}
}