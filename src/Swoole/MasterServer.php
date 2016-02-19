<?php

namespace DIServer\Swoole;

use DIServer\Interfaces\Swoole\IMasterServer;
use DIServer\Services\Log;
use DIServer\Services\Session;

/**
 * Description of MasterServer
 *
 * @author Back
 */
class MasterServer implements IMasterServer
{

	public function OnMasterStart(\swoole_server $server)
	{
		//Log::Notice("On Master Start");

		Session::Init();
	}

	public function OnMasterShutdown(\swoole_server $server)
	{
		//Log::Notice("On Master Shutdown");
	}


}
