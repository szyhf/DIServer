<?php

namespace DIServer\Services;

use DIServer\Interfaces\IMasterServer as IMasterServer;

/**
 * Description of MasterServer
 *
 * @author Back
 */
class MasterServer extends Service implements IMasterServer
{

	public function OnStart(\swoole_server $server)
	{
		echo("OnStart" . PHP_EOL);
	}

	public function OnShutdown(\swoole_server $server)
	{
		echo("OnShutdown" . PHP_EOL);
	}

}
