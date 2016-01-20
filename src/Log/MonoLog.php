<?php
/**
 * Created by PhpStorm.
 * User: Back
 * Date: 2015/12/29
 * Time: 11:24
 */

namespace DIServer\Log;

use DIServer\Log\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class MonoLog extends Log
{
	private $logger = null;

	public function __construct(\DIServer\Interfaces\IApplication $app)
	{
		parent::__construct($app);
		$this->logger = new Logger($this->getApp()->GetServerName());
	}

	public function SetLogType($type, $params)
	{
		switch($type)
		{
			case 'file':
			{
				$logPath = $params['path'];//$this->getApp()->GetBasePath() . '/Log/' . $this->getApp()->GetServerName() . '.log';
				$streamHandler = new StreamHandler($logPath);
				$this->logger->pushHandler($streamHandler);
				break;
			}
		}
	}

	/**
	 * 系统不可用
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function Emergency($message, array $context = [])
	{
		// TODO: Implement Emergency() method.
		$this->logger->addEmergency($message, $context);
	}

	/**
	 * **必须**立刻采取行动
	 * 例如：在整个网站都垮掉了、数据库不可用了或者其他的情况下，**应该**发送一条警报短信把你叫醒。
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function Alert($message, array $context = [])
	{
		// TODO: Implement Alert() method.
		$this->logger->addAlert($message, $context);
	}

	/**
	 * 紧急情况
	 * 例如：程序组件不可用或者出现非预期的异常。
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function Critical($message, array $context = [])
	{
		// TODO: Implement Critical() method.
		$this->logger->addCritical($message, $context);
	}

	/**
	 * 运行时出现的错误，不需要立刻采取行动，但必须记录下来以备检测。
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function Error($message, array $context = [])
	{
		// TODO: Implement Error() method.
		$this->logger->addError($message, $context);
	}

	/**
	 * 出现非错误性的异常。
	 * 例如：使用了被弃用的API、错误地使用了API或者非预想的不必要错误。
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function Warning($message, array $context = [])
	{
		// TODO: Implement Warning() method.
		$this->logger->addWarning($message, $context);
	}

	/**
	 * 一般性重要的事件。
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function Notice($message, array $context = [])
	{
		// TODO: Implement Notice() method.
		$this->logger->addNotice($message, $context);
	}

	/**
	 * 重要事件
	 * 例如：用户登录和SQL记录。
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function Info($message, array $context = [])
	{
		// TODO: Implement Info() method.
	}

	/**
	 * debug 详情
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function Debug($message, array $context = [])
	{
		// TODO: Implement Debug() method.
	}

	/**
	 * 任意等级的日志记录
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function Log($level, $message, array $context = [])
	{
		// TODO: Implement Log() method.
	}
}