<?php

namespace DIServer\Bootstraps;

/**
 * 检查运行环境
 *
 * @author Back
 */
class CheckEnvironment extends Bootstrap
{
	public function Bootstrap()
	{
		echo '=============================================================' . PHP_EOL;
		if(php_sapi_name() !== 'cli')
		{
			throw new BootException('App should run in CLI mode.' . PHP_EOL);
		}
		echo 'Is running in PHP-CLI mode.' . PHP_EOL;
		// 检测PHP环境
		if(version_compare(PHP_VERSION, '5.5.9', '<'))
		{
			throw new BootException('Require PHP version >= 5.5.9 !' . PHP_EOL);
		}
		echo "PHP version is    ".PHP_VERSION . PHP_EOL;
		// 检测Swoole环境
		if(version_compare(\swoole_version(), '1.7.20', '<'))
		{
			throw new BootException('Require swoole version >= 1.7.20' . PHP_EOL);
		}
		echo "Swoole version is " . \swoole_version() . PHP_EOL;
		// 检测服务名称
		if(!\defined('DI_SERVER_NAME'))
		{
			throw new BootException('DI_SERVER_NAME should set' . PHP_EOL);
		}
		echo "Server name is " . DI_SERVER_NAME . PHP_EOL;
		echo '=============================================================' . PHP_EOL;
	}

}
