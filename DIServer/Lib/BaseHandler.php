<?php
namespace DIServer;
/**
 * 基础Handler的实现
 *
 * @author Back
 */
abstract class BaseHandler 
{
    public function ID()
    {
	return $this->ID;
    }
    
    public function TaskID(&$handlerParams)
    {
	return -1;//-1表示不指定处理Task
    }
    
    public function __BeforeRun(&$data)
    {
	//一般情况下可以把HandlerID和Size去掉
	$data['params'] = substr($data['params'], 8);
    }
    
    public function Run(&$data)
    {
	
    }
    
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
