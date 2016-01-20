<?php

namespace DIServer\Services
{
	/**
	 * 启动器管理器
	 * （按配置加载并依序启动启动器）
	 *
	 * @package DIServer\Services
	 */
	class Bootstrapper extends Service
	{
		/**
		 * 执行启动器
		 */
		public function Boot()
		{
			$bootstraps = $this->initBootstraps();
			$this->bootWithBootstraps($bootstraps);
		}

		protected function initBootstraps()
		{
			/**
			 * DIServer默认启动器配置目录。
			 */
			return include $this->getApp()
			                    ->GetFrameworkPath() . '/Config/Bootstrap.php';
		}

		protected function bootWithBootstraps(array $bootstraps = [])
		{
			foreach($bootstraps as $boot)
			{
				/* @var $bootstrap \DIServer\Bootstraps\Bootstrap */
				$bootstrap = $this->getApp()
				                  ->BuildWithClass($boot);
				$bootstrap->Register();
				$bootstrap->BeforeBootstrap();
				$bootstrap->Bootstrap();
				$bootstrap->AfterBootstrap();
			}
		}

	}
}
