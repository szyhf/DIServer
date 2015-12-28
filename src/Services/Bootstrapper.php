<?php

namespace DIServer\Services
{
	class Bootstrapper extends Service
	{

		protected $bootstraps;

		public function Boot()
		{
			$this->bootstraps = include __DIR__ . '/../Config/Bootstrap.php';
			foreach($this->bootstraps as $boot)
			{
				/* @var $bootstrap \DIServer\Bootstraps\Bootstrap */
				$bootstrap = $this->getApp()->BuildWithClass($boot);
				$bootstrap->BeforeBootstrap();
				$bootstrap->Bootstrap();
				$bootstrap->AfterBootstrap();
			}
		}

	}
}
