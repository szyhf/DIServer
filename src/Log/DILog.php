<?php
namespace DIServer\Log;

use DIServer\Container\Container;
use DIServer\Interfaces\ILog;

class DILog implements ILog
{
	/**
	 * @return \swoole_server
	 */
	protected function getCurrentSwoole()
	{
		return Container::Instance()[\swoole_server::class];
	}

	/**
	 * 简单的打印一下Log
	 *
	 * @param type $msg
	 * @param type $level
	 */
	function DILog($msg, $level = "i")
	{
		if(DI_DAEMONIZE)
		{
			$msg = str_replace("\n", "\\n", $msg);
			echo date("[Y-m-d H:i:s]") . "[{$level}][" . posix_getpid() . "][" . $this->getCurrentSwoole()->worker_id . "] " . $msg . "\n";
		}
		else
		{
			$messages = explode("\n", $msg);
			foreach($messages as $msg)
			{
				echo date("[Y-m-d H:i:s]") . "[{$level}][" . posix_getpid() . '] ' . $msg . "\n";
			}

		}

		return $this;//支持连贯操作
	}

	/**
	 * 系统不可用（将被记为f）
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function Emergency($message, array $context = [])
	{
		return $this->ifLog(__FUNCTION__) ? $this->Log('f', $message, $context) : $this;
	}

	/**
	 * **必须**立刻采取行动
	 * 例如：在整个网站都垮掉了、数据库不可用了或者其他的情况下
	 * “应该”发送一条警报短信把你叫醒。
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function Alert($message, array $context = [])
	{
		return $this->ifLog(__FUNCTION__) ? $this->Log('a', $message, $context) : $this;
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
		return $this->ifLog(__FUNCTION__) ? $this->Log('c', $message, $context) : $this;
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
		return $this->ifLog(__FUNCTION__) ? $this->Log('e', $message, $context) : $this;
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
		return $this->ifLog(__FUNCTION__) ? $this->Log('w', $message, $context) : $this;
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
		return $this->ifLog(__FUNCTION__) ? $this->Log('n', $message, $context) : $this;
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
		return $this->ifLog(__FUNCTION__) ? $this->Log('i', $message, $context) : $this;
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
		return $this->ifLog(__FUNCTION__) ? $this->Log('d', $message, $context) : $this;
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
		$message = $this->formatMessage($message);
		$message = $this->interpolate($message, $context);
		$this->DILog($message, $level);

		return $this;//支持连贯操作
	}

	protected function ifLog($funcName = __FUNCTION__)
	{
		return $this->logLevel2int[$funcName] >= $this->logLevel;
	}

	/**
	 * Format the parameters for the logger.
	 * （来自Lavarel的Log类）
	 *
	 * @param  mixed $message
	 *
	 * @return mixed
	 */
	protected static function formatMessage($message)
	{
		if(is_array($message)||is_object($message))
		{
			return var_export($message, true);
		}
		elseif($message instanceof Jsonable)
		{
			return $message->toJson();
		}
		elseif($message instanceof Arrayable)
		{
			return var_export($message->toArray(), true);
		}

		return $message;
	}

	/**
	 * Interpolates context values into the message placeholders.
	 */
	protected static function interpolate($message, array $context = [])
	{
		// build a replacement array with braces around the context keys
		$replace = [];
		foreach($context as $key => $val)
		{
			$replace['{' . $key . '}'] = $val;
		}

		// interpolate replacement values into the message and return
		return strtr($message, $replace);
	}

	/**
	 * @var array 日志类型到等级的映射
	 */
	protected $logLevel2int = [
		'Emergency' => 7,
		'Alert'     => 6,
		'Critical'  => 5,
		'Error'     => 4,
		'Warning'   => 3,
		'Notice'    => 2,
		'Info'      => 1,
		'Debug'     => 0
	];

	const LOG_EMERGENCY = 'Emergency';
	const LOG_ALERT = 'Alert';
	const LOG_CRITICAL = 'Critical';
	const LOG_ERROR = 'Error';
	const LOG_WARNING = 'Warning';
	const LOG_NOTICE = 'Notice';
	const LOG_INFO = 'Info';
	const LOG_DEBUG = 'Debug';
}