<?php

namespace DIServer\Swoole;

use DIServer\Interfaces\Swoole\IMasterServer as IMasterServer;
use DIServer\Services\Log;
use DIServer\Services\Server;
use DIServer\Services\Service;

/**
 * Description of MasterServer
 *
 * @author Back
 */
class MasterServer extends Service implements IMasterServer
{

	public function OnStart(\swoole_server $server)
	{
		Log::Notice("On Master Start");
	}

	public function OnShutdown(\swoole_server $server)
	{
		Log::Instance()
		   ->Notice("On Master Shutdown");
	}

}
