<?php
/**
 * 日志工具配置
 */
return [
	'Driver'  => \Monolog\Logger::class,
	'Handler' => \Monolog\Handler\StreamHandler::class
];