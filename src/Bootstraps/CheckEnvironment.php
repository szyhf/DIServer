<?php

namespace DIServer\Bootstraps;

use \DIServer\Exceptions\BootException as BootException;

/**
 * 检查运行环境
 *
 * @author Back
 */
class CheckEnvironment extends Bootstrap
{
    public function Bootstrap()
    {
	parent::Bootstrap();
	if (php_sapi_name() !== 'cli')
	    throw new BootException('App should run in CLI mode.' . PHP_EOL);
	// 检测PHP环境
	if (version_compare(PHP_VERSION, '5.5.9', '<'))
	    throw new BootException('Require PHP version >= 5.5.9 !' . PHP_EOL);
	// 检测Swoole环境
	if (version_compare(\swoole_version(), '1.7.20', '<'))
	    throw new BootException('Require swoole version >= 1.7.20' . PHP_EOL);
	// 检测服务名称
	if (!\defined('DI_SERVER_NAME'))
	    throw new BootException('DI_SERVER_NAME should set' . PHP_EOL);
    }

}
