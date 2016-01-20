<?php
//应用需要的基础目录结构
return[
	DI_APP_COMMON_PATH,//应用公共目录
	DI_APP_COMMON_TICKER_PATH,//应用公共定时器目录

	DI_APP_SERVER_PATH,//服务基础目录
	DI_APP_SERVER_CONFIG_PATH,//服务基础配置
	DI_APP_SERVER_LISTENER_PATH,//监听配置
	DI_APP_SERVER_TICKER_PATH,//定时器目录

	DI_LOG_PATH,//swoole默认输入日志目录
];