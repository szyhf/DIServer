<?php
namespace DIServer;

/**
 * 自定义工作进程的原型
 *
 * @author Back
 */
class Process extends \swoole_process
{
    private $_server;

    public function __construct(\swoole_server &$server, $redirect_stdin_stdout = false, $create_pipe = true)
    {
	if ($server)
	{
	    $this->_server = $server;
	    parent::__construct([$this, 'OnWorkerStart'], $redirect_stdin_stdout, $create_pipe);
	}
	else
	{
	    die("BaseProcess->__construct error: \$server is null.\n");
	}
    }

    public function OnWorkerStart(\swoole_process $worker)
    {
	DILog('DIWorkerStart');
    }

}
