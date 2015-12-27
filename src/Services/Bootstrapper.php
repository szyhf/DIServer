<?php

namespace DIServer\Services
{

	use DIServer\Interfaces\IBootstrapper as IBootstrapper;

	class Bootstrapper extends Service implements IBootstrapper
	{

		protected $bootstraps;

		public function Boot()
		{
			$this->bootstraps = include __DIR__ . '/../Config/Bootstrap.php';
			foreach($this->bootstraps as $boot)
			{
				/* @var $bootstrap \DIServer\Bootstraps\Bootstrap */
				$bootstrap = $this->GetApp()->BuildWithClass($boot);
				$bootstrap->BeforeBootstrap();
				$bootstrap->Bootstrap();
				$bootstrap->AfterBootstrap();
			}
		}

	}
}
