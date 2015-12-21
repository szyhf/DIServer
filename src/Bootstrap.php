<?php

namespace DIServer;

if (php_sapi_name() !== 'cli')
    die('Should run in CLI mode.');

// 检测PHP环境
if (version_compare(PHP_VERSION, '5.5.0', '<'))
    die('Require PHP > 5.5.0 !');
// 检测Swoole环境
if (version_compare(\swoole_version(), '1.7.20', '<'))
    die('Require swoole > 1.7.20');

\defined('DI_DISERVER_PATH') or \define('DI_DISERVER_PATH', __DIR__);
require_once DI_DISERVER_PATH . '/Vendor/AutoLoader.php';
$container = \DIServer\Lib\DI\DIContainer\DIContainer::Container();

$container->RegisterClass('DIServer\NewDIServer');
$server = $container->BuildWithClass('DIServer\NewDIServer');