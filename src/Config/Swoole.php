<?php

/**
 * 由于Swoole服务一旦启动就不能修改配置，所以这个配置中的参数修改以后无法通过Reload重载。
 * 设置为''表示采用swoole_server的默认设置。
 *
 * @link http://wiki.swoole.com swoole服务的配置，请参考手册
 */
return [
	'reactor_num'              => '', //使用的CPU核心数，不设置表示根据机器选择（建议为设置为CPU核数*2）
	'worker_num'               => 1, //启动的worker进程数（建议设置为CPU的1-4倍最合理）
	'max_request'              => 1000, //每个Worker处理的最大任务数，超过后会自动重启该Worker（不希望进程自动退出可以设置为0）
	'max_conn'                 => 65535, //服务器程序，最大允许并发的连接数，不设置表示使用操作系统设置上限（ulimit -n的值），超量的连接会被拒绝。
	'task_worker_num'          => 1, //配置task进程的数量，配置此参数后将会启用task功能（DIServer必须开启此功能）。
	'task_ipc_mode'            => 2, //设置task进程与worker进程之间通信的方式，1使用unix socket通信，2使用消息队列通信，3使用消息队列通信，并设置为争抢模式
	'task_max_request'         => 1000, //task进程的最大任务数,不希望进程自动退出可以设置为0
	'task_tmpdir'              => DI_APP_SERVER_TEMP_PATH, //设置task的数据临时目录，在swoole_server中，如果投递的数据超过8192字节，将启用临时文件来保存数据。
	'dispatch_mode'            => 2, //1轮循模式，2固定模式，3抢占模式，4IP分配，5UID分配。
	'message_queue_key'        => ftok(DI_APP_SERVER_PATH, 3), //设置消息队列的KEY
	'daemonize'                => DI_DAEMONIZE,//是否作为守护进程
	'backlog'                  => 128, //Listen队列长度，此参数将决定最多同时有多少个等待accept的连接。
	'log_file'                 => DI_LOG_PATH . '/' . DI_LOG_FILE_NAME, //指定swoole的日志文件（启用守护进程后，标准输入和输出会被重定向到 log_file）。
	'heartbeat_check_interval' => 60, //启用心跳检测，此选项表示每隔多久轮循一次，单位为秒。
	'heartbeat_idle_time'      => 120, //表示连接最大允许空闲（没消息）的时间
	'open_eof_check'           => false, //打开EOF检测，此选项将检测客户端连接发来的数据，当数据包结尾是指定的字符串时才会投递给Worker进程。
	'open_eof_split'           => false, //启用后，底层会从数据包中间查找EOF，并拆分数据包。
	'package_eof'              => '', //与open_eof_check配合使用，设置EOF字符串。
	'open_length_check'        => true, //打开包长检测特性。包长检测提供了固定包头+包体这种格式协议的解析。
	'package_length_type'      => 'L', //长度值的类型，接受一个字符参数，与php的pack函数一致。
	'package_max_length'       => 1024 * 1024 * 10, //一个数据包最大允许占用的内存尺寸，超出的数据包会被丢弃。
	'open_cpu_affinity'        => '',
	//启用CPU亲和性设置。在多核的硬件平台中，启用此特性会将swoole的reactor线程/worker进程绑定到固定的一个核上。可以避免进程/线程的运行时在多个核之间互相切换，提高CPU Cache的命中率。
	'cpu_affinity_ignore'      => '', //接受一个数组作为参数，array(0, 1) 表示不使用CPU0,CPU1，专门空出来处理网络中断。
	'open_tcp_nodelay'         => true, //启用open_tcp_nodelay，开启后TCP连接发送数据时会关闭Nagle合并算法，立即发往客户端连接。
	'tcp_defer_accept'         => '', //用处不明
	'ssl_cert_file'            => '', //设置SSL隧道加密，设置值为一个文件名字符串，制定cert证书和key的路径。
	'user'                     => '', //设置worker/task子进程的所属用户。
	'group'                    => '', //设置worker/task子进程的进程用户组。
	//'chroot'                   => DI_APP_PATH, //重定向Worker进程的文件系统根目录（会影响autoloader，慎用）
	'pipe_buffer_size'         => '', //调整管道通信的内存缓存区长度,在1.7.17以上版本默认为32M
	'buffer_output_size'       => 1024 * 1024 * 10, //调整连接发送缓存区的大小。
	'enable_unsafe_event'      => false,
	//在配置dispatch_mode=1或3后，因为系统无法保证onConnect/onReceive/onClose的顺序，默认关闭了onConnect/onClose事件。
	'discard_timeout_request'  => true, //为true，表示如果worker进程收到了已关闭连接的数据请求，将自动丢弃。
	'enable_reuse_port'        => false//启用端口重用后可以重复启动同一个端口的Server程序
];
