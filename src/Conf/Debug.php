<?php
//Debug模式下，该配置会覆盖Convention.php的相同选项
return [
    'SW_WORKER_NUM' => 1, //启动的worker进程数（建议设置为CPU的1-4倍最合理）
    'SW_MAX_REQUEST' => 0, //每个Worker处理的最大任务数，超过后会自动重启该Worker（不希望进程自动退出可以设置为0）
    'SW_MAX_CONN' => 65535, //服务器程序，最大允许并发的连接数，不设置表示使用操作系统设置上限（ulimit -n的值），超量的连接会被拒绝。
];
