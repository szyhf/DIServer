<?php

namespace DIServer\Swoole;

use DIServer\Interfaces\Swoole\IMasterServer as IMasterServer;
use DIServer\Services\Service as Service;
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
