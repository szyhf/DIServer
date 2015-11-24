<?php

//惯例配置
return [    
    'DB_PING_INTERVAL' => 0, //是否定期跟数据库交流感情，为0表示不设置
    'DB_PING_WORKER_NUM' => 0, //承担与数据库交流感情的Worker的ID
    'USER_INFO_EX' => 128, //用户信息的保存时长
    'HANDLER_SLOW_CHECK' => 1, //一个Handler执行多长时间(秒）记为慢速Handler
    'SW_DI_SETTING_MAP' => //DIServer设置中与Swoole相关设置的映射
    [
	'reactor_num' => 'SW_RECTOR_NUM',
	'worker_num' => 'SW_WORKER_NUM',
	'max_request' => 'SW_MAX_REQUEST',
	'max_conn' => 'SW_MAX_CONN',
	'task_worker_num' => 'SW_TASK_WORKER_NUM',
	'task_ipc_mode' => 'SW_TASK_IPC_MODE',
	'task_max_request' => 'SW_TASK_MAX_REQUEST',
	'task_tmpdir' => 'SW_TASK_TMPDIR',
	'dispatch_mode' => 'SW_DISPATCH_MODE',
	'message_queue_key' => 'SW_MESSAGE_QUEUE_KEY',
	'daemonize' => 'SW_DAEMONIZE',
	'backlog' => 'SW_BACKLOG',
	'log_file' => 'SW_LOG_FILE',
	'heartbeat_check_interval' => 'SW_HEARTBEAT_CHECK_INTERVAL',
	'heartbeat_idle_time' => 'SW_HEARTBEAT_IDLE_TIME',
	'open_eof_check' => 'SW_EOF_CHECK',
	'open_eof_split' => 'SW_EOF_SPLIT',
	'package_eof' => 'SW_PACKAGE_EOF',
	'open_length_check' => 'SW_OPEN_LENGTH_CHECK',
	'package_length_type' => 'SW_PACKAGE_LENGTH_TYPE',
	'package_max_length' => 'SW_PACKAGE_MAX_LENGTH',
	'open_cpu_affinity' => 'SW_OPEN_CPU_AFFINITY',
	'cpu_affinity_ignore' => 'SW_AFFINITY_IGNORE',
	'open_tcp_nodelay' => 'SW_OPEN_TCP_NODELAY',
	'tcp_defer_accept' => 'SW_TCP_DEFER_ACCEPT',
	'ssl_cert_file' => 'SW_SSL_CERT_FILE',
	'user' => 'SW_USER',
	'group' => 'SW_GROUP',
	'chroot' => 'SW_CHROOT',
	'pipe_buffer_size' => 'SW_PIPE_BUFFER_SIZE',
	'buffer_output_size' => 'SW_BUFFER_OUTPUT_SIZE',
	'enable_unsafe_event' => 'SW_ENABLE_UNSAFE_EVENT',
	'discard_timeout_request' => 'SW_DISCARD_TIMEOUT_REQUEST',
	'enable_reuse_port'=>'SW_ENABLE_REUSE_PORT'
    ]
    ,
    'SW_RECTOR_NUM' => '', //使用的CPU核心数，不设置表示根据机器选择（建议为设置为CPU核数*2）
    'SW_WORKER_NUM' => 1, //启动的worker进程数（建议设置为CPU的1-4倍最合理）
    'SW_MAX_REQUEST' => 0, //每个Worker处理的最大任务数，超过后会自动重启该Worker（不希望进程自动退出可以设置为0）
    'SW_MAX_CONN' => 65535, //服务器程序，最大允许并发的连接数，不设置表示使用操作系统设置上限（ulimit -n的值），超量的连接会被拒绝。
    'SW_TASK_WORKER_NUM' => 1, //配置task进程的数量，配置此参数后将会启用task功能（DIServer必须开启此功能）。
    'SW_TASK_IPC_MODE' => 2, //设置task进程与worker进程之间通信的方式，1使用unix socket通信，2使用消息队列通信，3使用消息队列通信，并设置为争抢模式
    'SW_TASK_MAX_REQUEST' => 0, //task进程的最大任务数,不希望进程自动退出可以设置为0
    'SW_TASK_TMPDIR' => '', //设置task的数据临时目录，在swoole_server中，如果投递的数据超过8192字节，将启用临时文件来保存数据。
    'SW_DISPATCH_MODE' => 2, //1轮循模式，2固定模式，3抢占模式，4IP分配，5UID分配。
    'SW_MESSAGE_QUEUE_KEY' => '', //设置消息队列的KEY
    'SW_DAEMONIZE' => 0, //守护进程化。
    'SW_BACKLOG' => 128, //Listen队列长度，此参数将决定最多同时有多少个等待accept的连接。
    'SW_LOG_FILE' => DI_LOG_PATH . '/' . DI_LOG_FILE_NAME, //指定swoole的日志文件（启用守护进程后，标准输入和输出会被重定向到 log_file）。
    'SW_HEARTBEAT_CHECK_INTERVAL' => 60, //启用心跳检测，此选项表示每隔多久轮循一次，单位为秒。
    'SW_HEARTBEAT_IDLE_TIME' => 120, //表示连接最大允许空闲（没消息）的时间
    'SW_EOF_CHECK' => FALSE, //打开EOF检测，此选项将检测客户端连接发来的数据，当数据包结尾是指定的字符串时才会投递给Worker进程。
    'SW_EOF_SPLIT' => FALSE, //启用后，底层会从数据包中间查找EOF，并拆分数据包。
    'SW_PACKAGE_EOF' => '', //与open_eof_check配合使用，设置EOF字符串。
    'SW_OPEN_LENGTH_CHECK' => TRUE, //打开包长检测特性。包长检测提供了固定包头+包体这种格式协议的解析。
    'SW_PACKAGE_LENGTH_TYPE' => 'L', //长度值的类型，接受一个字符参数，与php的pack函数一致。
    'SW_PACKAGE_MAX_LENGTH' => 1024*1024*10, //一个数据包最大允许占用的内存尺寸，超出的数据包会被丢弃。
    'SW_OPEN_CPU_AFFINITY' => '', //启用CPU亲和性设置。在多核的硬件平台中，启用此特性会将swoole的reactor线程/worker进程绑定到固定的一个核上。可以避免进程/线程的运行时在多个核之间互相切换，提高CPU Cache的命中率。
    'SW_AFFINITY_IGNORE' => '', //接受一个数组作为参数，array(0, 1) 表示不使用CPU0,CPU1，专门空出来处理网络中断。
    'SW_OPEN_TCP_NODELAY' => TRUE, //启用open_tcp_nodelay，开启后TCP连接发送数据时会关闭Nagle合并算法，立即发往客户端连接。
    'SW_TCP_DEFER_ACCEPT' => '', //用处不明
    'SW_SSL_CERT_FILE' => '', //设置SSL隧道加密，设置值为一个文件名字符串，制定cert证书和key的路径。
    'SW_USER' => '', //设置worker/task子进程的所属用户。
    'SW_GROUP' => '', //设置worker/task子进程的进程用户组。
    'SW_CHROOT' => '', //重定向Worker进程的文件系统根目录。
    'SW_PIPE_BUFFER_SIZE' => '', //调整管道通信的内存缓存区长度,在1.7.17以上版本默认为32M
    'SW_BUFFER_OUTPUT_SIZE' => 1024 * 1024 * 10, //调整连接发送缓存区的大小。
    'SW_ENABLE_UNSAFE_EVENT' => FALSE, //在配置dispatch_mode=1或3后，因为系统无法保证onConnect/onReceive/onClose的顺序，默认关闭了onConnect/onClose事件。
    'SW_DISCARD_TIMEOUT_REQUEST' => TRUE,//为true，表示如果worker进程收到了已关闭连接的数据请求，将自动丢弃。
    'SW_ENABLE_REUSE_PORT'=>FALSE
];
