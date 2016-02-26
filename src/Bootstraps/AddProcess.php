<?php

namespace DIServer\Bootstraps;


use DIServer\Helpers\Ary;
use DIServer\Services\Application;
use DIServer\Services\Container;
use DIServer\Services\Log;
use DIServer\Services\Server;

class AddProcess extends Bootstrap
{
	public function Bootstrap()
	{
		$processes = Application::AutoBuildCollection('Process.php', \swoole_process::class);
		foreach($processes as $processClass => $process)
		{
			if(Server::AddProcess($process))
			{
				Log::Notice("User process {processClass} has added.", ['processClass' => get_class($process)]);
			}
		}
	}
}