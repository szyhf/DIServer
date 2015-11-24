<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer\Ticker;

use DIServer\BaseTicker;

/**
 * 定期与数据库沟通感情的定时器
 * 防止数据库断开连接
 *
 * @author Back
 */
class DBPingTicker extends BaseTicker
{
    public function TryBind(BaseReloadHelper &$reloadHelper, \swoole_server &$server, &$worker_id)
    {	
	if ($worker_id == C('DB_PING_WORKER_NUM') && C('DB_PING_INTERVAL'))
	{
	    //设置定时数据库交流
	    swoole_timer_tick(C('DB_PING_INTERVAL'), [$this, 'TickDBServerPing'], $server);
	    DILog("DBPingTicker Is Set On Worker[{$worker_id}]Interval " . C('DB_PING_INTERVAL') . "ms.");
	}
    }

    /**
     * 定期跟数据库交流一下感情防止感情破裂
     */
    public function TickDBServerPing($timer_id, $params = null)
    {
	if ($params)
	{
	    if (!D()->execute('select 0;'))
		DILog('数据库，卒');
	}
    }

}
