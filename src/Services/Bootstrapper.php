<?php

namespace DIServer\Services
{

	use DIServer\Services\Application;

	/**
	 * 启动器管理器
	 * （按配置加载并依序启动启动器）
	 *
	 * @package DIServer\Services
	 */
	class Bootstrapper extends Facade
	{
		protected static function getFacadeAccessor()
		{
			return \DIServer\Interfaces\IBootstrapper::class;
		}

		/**
		 * 执行启动器
		 */
		public static function Boot()
		{
			$bootstraps = self::initBootstraps();
			self::bootWithBootstraps($bootstraps);
		}

		protected static function initBootstraps()
		{
			/**
			 * DIServer默认启动器配置目录。
			 */
			return include Application::GetFrameworkPath() . '/Config/Bootstrap.php';
		}

		protected static function bootWithBootstraps(array $bootstraps = [])
		{
			foreach($bootstraps as $boot)
			{
				/* @var $bootstrap \DIServer\Bootstraps\Bootstrap */
				$bootstrap = Application::BuildWithClass($boot);
				$bootstrap->BeforeBootstrap();
				$bootstrap->Bootstrap();
				$bootstrap->AfterBootstrap();
			}
		}

	}
}
