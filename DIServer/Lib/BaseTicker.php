<?php

namespace DIServer;
use DIServer\BaseReloadHelper;
/**
 * Worker定时器的基本模板
 * Ticker是运行在Worker进程的服务，请不要在此执行较复杂的业务逻辑
 * 否则可能引起Worker阻塞或丢失定时信号。
 * 业务可使用DICallHandler转发给Task进程的Handler进行处理。
 * 把Ticker看作一个闹钟就好
 * @author Back
 */
class BaseTicker
{
    /**
     * 尝试绑定并激活定时器，请在子类自行实现绑定功能及绑定后的回调方法
     * @param BaseReloadHelper $reloadHelper
     * @param \swoole_server $server
     * @param type $worker_id
     * @return mix
     */
    public function TryBind(BaseReloadHelper &$reloadHelper, \swoole_server &$server, &$worker_id)
    {
	return false;
    }
}
