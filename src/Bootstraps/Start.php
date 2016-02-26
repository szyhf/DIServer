<?php

namespace DIServer\Bootstraps;

use DIServer\Services\Server;

class Start extends Bootstrap
{
	public function Bootstrap()
	{
		Server::Start();
	}
}