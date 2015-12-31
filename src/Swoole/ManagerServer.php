<?php

namespace DIServer\Swoole;

use DIServer\Interfaces\Swoole\IManagerServer as IManagerServer;
use DIServer\Services\Service as Service;

/**
 * Description of ManagerServer
 *
 * @author Back
 */
class ManagerServer extends Service implements IManagerServer
{

	public function OnManagerStart(\swoole_server $server)
	{
		//Log('Server Start');
		echo("OnManagerStart" . PHP_EOL);
		//如果使用FileSession，应把Runtime目录清空。
	}

	public function OnManagerStop(\swoole_server $server)
	{
		//Log('Server Stop');
		echo("OnManagerStop" . PHP_EOL);
	}

}
