<?php

namespace DIServer\Services;

use DIServer\Interfaces\IManagerServer as IManagerServer;

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
	}

	public function OnManagerStop(\swoole_server $server)
	{
		//Log('Server Stop');
		echo("OnManagerStop" . PHP_EOL);
	}

}
