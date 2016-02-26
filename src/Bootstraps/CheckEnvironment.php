<?php

namespace DIServer\Bootstraps;

use DIServer\Services\Log;

/**
 * 检查运行环境
 *
 * @author Back
 */
class CheckEnvironment extends Bootstrap
{
	public function Bootstrap()
	{
		//echo '=============================================================' . PHP_EOL;
		$envAry = [];
		$colorPrefix = "";//"\033[31m";
		$colorSufix = "";//"\033[0m";
		if(php_sapi_name() !== 'cli')
		{
			throw new BootException('App should run in CLI mode.' . PHP_EOL);
		}
		$envAry['Running mode'] = 'PHP-CLI';
		// 检测PHP环境
		if(version_compare(PHP_VERSION, '5.5.9', '<'))
		{
			throw new BootException('Require PHP version >= 5.5.9 !' . PHP_EOL);
		}
		$envAry['PHP version'] = $colorPrefix . PHP_VERSION . $colorSufix;
		// 检测Swoole环境
		if(version_compare(\swoole_version(), '1.8.1', '<'))
		{
			throw new BootException('Require swoole version >= 1.8.1' . PHP_EOL);
		}
		$envAry['Swoole version'] = $colorPrefix . \swoole_version() . $colorSufix;
		// 检测服务名称
		if(!\defined('DI_SERVER_NAME'))
		{
			throw new BootException('DI_SERVER_NAME should set' . PHP_EOL);
		}
		$envAry['Server name'] = $colorPrefix . DI_SERVER_NAME . $colorSufix;
		//echo '=============================================================' . PHP_EOL;
		Log::Info(['Environment' => $envAry]);
	}

}
