<?php

namespace DIServer\Swoole;

use DIServer\Interfaces\Swoole\IManagerServer as IManagerServer;
use DIServer\Services\Log;
use DIServer\Services\Service;

/**
 * Description of ManagerServer
 *
 * @author Back
 */
class ManagerServer implements IManagerServer
{

	public function OnManagerStart(\swoole_server $server)
	{
		//Log::Notice("On Manager Start.");
	}

	public function OnManagerStop(\swoole_server $server)
	{
		//Log::Notice("On Manager Stop");
	}
}
