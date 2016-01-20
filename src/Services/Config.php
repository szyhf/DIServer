<?php

namespace DIServer\Services;


use DIServer\Interfaces\IConfig;
use \DIServer\Interfaces\IApplication;

/**
 * 配置文件服务抽象类
 *
 * @package DIServer\Services
 */
abstract class Config extends Service implements IConfig
{
	/**
	 * @var
	 */
	protected $currentServer;
	public function __construct(IApplication $app, \swoole_server $server)
	{
		parent::__construct($app);
	}
}