<?php

namespace DIServer\Services;

use \DIServer\Interfaces\IBootstrapper as IBootstrapper;
use \DIServer\Application as Application;

class Bootstrapper extends Service implements IBootstrapper
{

    protected $bootstraps;

    public function Boot()
    {
	$this->bootstraps = include __DIR__ . '/../Config/Bootstrap.php';
	foreach ($this->bootstraps as $boot)
	{
	    /* @var $bootstrap \DIServer\Bootstraps\Bootstrap */
	    $bootstrap = $this->App()->IOC()->BuildWithClass($boot);
	    $bootstrap->Bootstrap();
	}
    }

}
