<?php

namespace DIServer;

/**
 * 基础Handler的实现
 *
 * @author Back
 */
abstract class Handler
{
    /* @var $server \swoole_server */
    protected $server;
    /**
     * 初始化Handler
     * @param \swoole_server $server 注入当前进程的$server对象
     */
    public function __construct(&$server = null)
    {
	$className = array_pop(explode('\\', get_called_class()));
	//如果ID被子类设置了，则沿用子类的设置；如果没有，尝试从配置文件中获取;
	$this->ID = is_numeric($this->ID) ? $this->ID : C($className . 'ID');
	//HandlerID应该被设置好
	if ($this->ID() === null)
	{
	    DILog(\get_called_class() . '.ID wasn\'t set or configured, this handler won\'t be loaded.', 'w');
	}
	//HandlerID必须是数字
	elseif (!is_numeric($this->ID()))
	{
	    DILog(\get_called_class() . '.ID isn\'t numeric, this handler won\'t be loaded.', 'w');
	}else
	{
	    $this->server = $server;
	}
    }

    public function ID()
    {
	return $this->ID;
    }

    public function TaskID(&$handlerParams)
    {
	return -1; //-1表示不指定处理Task
    }

    /**
     * 在执行Run函数之前执行。
     * @param array $data
     */
    public function __BeforeRun(&$data)
    {
	//一般情况下可以把HandlerID和Size去掉
	$data['params'] = substr($data['params'], 8);
    }

    public function Run(&$data)
    {
	
    }

    /**
     * 在执行Run函数之后执行。
     * @param array $data
     */
    public function __AfterRun(&$data)
    {
	
    }

    /**
     * 便于调试时把运行速度过慢的Handler当前执行的信息打印出来。
     * 需要使用者自己覆写并打印有用的信息
     * @param type $data
     */
    public function SlowLog(&$data)
    {
	
    }

}
