<?php

namespace Think\Cache\Driver;

use Think\Cache;

/**
 * 阿里云KVStroe专用缓存=。=
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 * @author Back
 */
class KVStore extends Cache
{

    protected $options = [];
    public $handler;

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options = array())
    {
	ini_set('default_socket_timeout', -1); //不超时
	if (empty($options))
	{
	    $options = array(
		'host' => C('KVSTORE_HOST'),
		'port' => C('KVSTORE_PORT'),
		'timeout' => false,
		'persistent' => false,
		'prefix' => C('DATA_CACHE_PREFIX'),
		'user' => C('DATA_CACHE_USER'),
		'pwd' => C('DATA_CACHE_PW'),
		'expire' => 12000
	    );
	}
	else
	{
	    $options['host'] = isset($options['host'])? : C('KVSTORE_HOST');
	    $options['port'] = isset($options['port'])? : C('KVSTORE_PORT');
	    $options['timeout'] = isset($options['timeout'])? : FALSE;
	    $options['persistent'] = isset($options['persistent'])? : FALSE;
	    $options['prefix'] = isset($options['prefix'])? : C('DATA_CACHE_PREFIX');
	    $options['user'] = isset($options['user'])? : C('DATA_CACHE_USER');
	    $options['pwd'] = isset($options['pwd'])? : C('DATA_CACHE_PW');
	    $options['expire'] = isset($options['expire'])? : 12000;
	}
	$this->options = $options;
	$redis = new \Redis();
	for ($i = 0; $i < 2; $i++)
	{
	    if ($redis->pconnect($options['host'], $options['port']) == false)
	    {
		try
		{
		    $error = $redis->getLastError();
		}
		catch (\RedisException $ex)
		{
		    $error = $ex->getMessage();
		    DILog($error);
		    if ($error != 'Redis server went away')
		    {
			break; //跳出重试循环
		    }
		    else
		    {
			sleep(0.5); //暂停0.5s以后重试连接
			DILog('Kvstore: Try Reconnected');
		    }
		}
//	    E($redis->getLastError());
	    }
	}
	$redis->setOption(\Redis::OPT_PREFIX, $options['prefix']);
	/* user:password 拼接成AUTH的密码 */
	if ($redis->auth($options['user'] . ':' . $options['pwd']) == false)
	{
	    $error = $redis->getLastError();
	    DILog($error);
//	    E($redis->getLastError());
	}
	$this->handler = $redis;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name)
    {
	$value = $this->handler->get($name);
	$jsonData = json_decode($value, true);
	return ($jsonData === NULL) ? $value : $jsonData; //检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null)
    {
	if (is_null($expire))
	{
	    $expire = isset($this->options['expire']) ? $this->options['expire'] : 0;
	}

	//对数组/对象数据进行缓存处理，保证数据完整性
	$value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
	if (is_int($expire) && $expire)
	{
	    $result = $this->handler->setex($name, $expire, $value);
	}
	else
	{
	    $result = $this->handler->set($name, $value);
	}
//	if ($result && $this->options['length'] > 0)
//	{
//	    // 记录缓存队列
//	    $this->queue($name);
//	}
	return $result;
    }

    /**
     * 调用rpush方法压入一个数据
     * @param type $key
     * @param type $value
     */
    public function push($key, $value)
    {
	$key = $key;
	//对数组/对象数据进行缓存处理，保证数据完整性
	$value = (is_object($value) || is_array($value)) ? json_encode($value) : $value;
	$result = $this->handler->sadd($key, $value);
	return $result;
    }

    public function pop($key)
    {
	$key = $key;
	return $this->handler->sPop($key);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
	return $this->handler->delete($name);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear()
    {
	return $this->handler->flushDB();
    }

}
