<?php

namespace DIServer\Handler;

use DIServer\Interfaces\IApplication;
use DIServer\Interfaces\IHandler;
use DIServer\Interfaces\IRequest;
use DIServer\Services\Event;
use DIServer\Services\Log;
use DIServer\Services\Service;

class Base extends Service implements IHandler
{
	/**
	 * @var array [$handlerID=>$handler]
	 */
	private static $_handlerArray = [];
	private $_swooleServer = null;
	private $_startTime;
	private $_endTime;

	/**
	 * @return swoole_server
	 */
	protected function getSwooleServer()
	{
		return $this->_swoole_server;
	}

	protected function task($task)
	{
		/** @var \swoole_table $statics */
		Event::Listen('BeforeTaskSend', [$task]);
		$taskID = $this->getSwooleServer()
		            ->task($task);
		Event::Listen('AfterTaskSend', [$task, $taskID]);

		return $taskID;
	}

	protected function taskWait($task)
	{
		Event::Listen('OnTaskSend', [$task]);

		return $this->getSwooleServer()
		            ->taskwait($task);
	}

	public function __construct(IApplication $app, \swoole_server $server)
	{
		parent::__construct($app);
		$this->_swoole_server = $server;
	}

	public function BeforeHandle(IRequest $request)
	{
		$this->_startTime = microtime(true);
	}

	public function Handle(IRequest $request)
	{
		// TODO: Implement Handle() method.
	}

	public function AfterHandle(IRequest $request)
	{
		$this->_endTime = microtime(true);
		if($this->_endTime - $this->_startTime > 1)
		{
			Log::Debug("Handler " . get_class($this) . " used " . ($this->_endTime - $this->_startTime . "s."));
		}
	}
}