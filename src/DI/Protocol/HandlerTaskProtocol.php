<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HandlerOnTaskProtocol
 *
 * @author Back
 */
abstract class HandlerTaskProtocol 
{
    public function OnTask(\swoole_server $server, $task_id, $from_id, $param)
    {
	/* @var $handler \DIServer\Handler */
	$handler = DIHandler($param['handlerID'])[$param['handlersKey']];
	if (!$handler)
	{
	    DILog("Calling On not exist HandlerID " .$param['handlersKey'].".".$param['handlerID']. ".",'d');
	    return;
	}
//	$info = $server->connection_info($data['fd'], $from_id);
	$param['taskID'] = $task_id;
	$param['fromID'] = $from_id;
	$param['server'] = &$server;
	G('HandlerStart');
	$handler->__BeforeRun($param);
	$handler->Run($param);
	$handler->__AfterRun($param);
	G('HandlerEnd');
	$timeUsed = G('HandlerStart', 'HandlerEnd');
	if ($timeUsed >= C('HANDLER_SLOW_CHECK'))
	{
	    $handler->SlowLog($param); //允许用户自定义慢处理参数日志
	    DILog(get_class($handler) . " use " . $timeUsed . "s on task {$task_id}", 'w');
	}
    }
    /**
     * 根据包命令将工作交给不同的Handler，如果有多个Handler可以响应，则不同的Handler可能在不同的Task中响应
     * @param \swoole_server $server Swoole_Server对象
     * @param type $data 接收到的完整的数据包
     * @param type $fd 连接的标识
     * @param type $task_id 
     * @param type $from_id
     */
    function HandleOnTask(\swoole_server &$server, &$data, $fd, $from_id)
    {

	//确定服务端应该用什么Handler来解析
	$cliHandlerID = \DIServer\Package::GetHandlerID($data);
//	$cliParams = \DIServer\Package::GetParams($data);
//	DILog("{$cliHandlerID}\\{$cliParams}");
//	DILog("Count<handler>".count($this->handlerArray));
	if ($cliHandlerID !== NULL)
	{
	    $handlerParams = [
		'fd' => $fd, //客户端的唯一标识，用于回发信息
		'params' => $data//客户端的完整报文
	    ]; //传入标准的解析参数
	    DICallHandler($cliHandlerID, $server, $handlerParams);
	}
    }
}
