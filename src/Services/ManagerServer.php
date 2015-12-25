<?php

namespace DIServer\Services;

use \DIServer\Interfaces\IManagerServer as IManagerServer;

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
	var_dump("Server Start");
    }

    public function OnManagerStop(\swoole_server $server)
    {
	//Log('Server Stop');
	var_dump("Server Stop");
    }

}
