<?php

return [
    'DI_SETTINGS' => [
	//'reactor_num' => 2,//使用的CPU核心数，不设置表示根据机器选择
	'worker_num' => 1, //启动的worker进程数（还是根据CPU核心数设置比较好）
	'max_request' => 100, //worker进程的最大任务数。一个worker进程在处理完超过此数值的任务后将自动退出，然后重启（防内存溢出）。
	//'max_connection'=>65535,//服务器程序，最大允许的连接数，不设置表示使用操作系统设置上限（ulimit -n的值）
	'task_worker_num' => 1, //配置task进程的数量，配置此参数后将会启用task功能。
	'task_ipc_mode' => 2, //设置task进程与worker进程之间通信的方式，2表示争抢模式
	'task_max_request' => 500, //设置task进程的最大任务数。一个worker进程在处理完超过此数值的任务后将自动退出，然后重启（防内存溢出）。
	'task_tmpdir' => './Temp', //设置task的数据临时目录
	'message_queue_key' => ftok(__FILE__, 1), //多个Server并发时，用于唯一标志消息队列
	'dispatch_mode' => 2,
	'daemonize' => 0, //守护进程模式（正式运行时使用）
	'backlog' => 128, //Listen队列长度
//	'log_file' => './Log/' . DI_LOG_FILE_NAME, //运行日志文件路径
	'heartbeat_check_interval' => 64, //心跳监测频率
	'heartbeat_idle_time' => 128, //死链判别标准
	'package_max_length' => 1024, //可接受的最大数据包
	//'chroot' => './tmp/root', //重定向目录，防止越权
//	'user' => 'WGDev', //重定向用户
//	'group' => 'www-data', //重定向用户组
	'open_length_check' => true,
	'package_length_type' => 'L',
	'package_length_offset' => 0,
	'package_body_offset' => 0,
	'package_max_length' => 800000,
	'buffer_output_size' => 1024 * 1024 * 10//数据发送缓存区10MB
    ]
];
