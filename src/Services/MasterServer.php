<?php

namespace DIServer\Services;

use \DIServer\Interfaces\IMasterServer as IMasterServer;

/**
 * Description of MasterServer
 *
 * @author Back
 */
class MasterServer extends Service implements IMasterServer
{

    public function OnStart(\swoole_server $server)
    {
	
    }

    public function OnShutdown(\swoole_server $server)
    {
	
    }

}
