<?php
namespace DIServer\Services;
use DIServer\Interfaces\ILog;

/**
 * Facade of ILog
 *
 * @package DIServer\Services
 */
class Log extends Facade
{
	protected static function getFacadeAccessor()
	{
		return ILog::class;
	}

	/**
	 * 系统不可用
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return ILog
	 */
	public static function Emergency($message, array $context = [])
	{
		call_user_func_array([self::getFacadeRoot(), __FUNCTION__], [$message, $context]);
	}

	/**
	 * **必须**立刻采取行动
	 * 例如：在整个网站都垮掉了、数据库不可用了或者其他的情况下，**应该**发送一条警报短信把你叫醒。
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return ILog
	 */
	public static function Alert($message, array $context = [])
	{
		call_user_func_array([self::getFacadeRoot(), __FUNCTION__], [$message, $context]);
	}

	/**
	 * 紧急情况
	 * 例如：程序组件不可用或者出现非预期的异常。
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return ILog
	 */
	public static function Critical($message, array $context = [])
	{
		call_user_func_array([self::getFacadeRoot(), __FUNCTION__], [$message, $context]);
	}

	/**
	 * 运行时出现的错误，不需要立刻采取行动，但必须记录下来以备检测。
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return ILog
	 */
	public static function Error($message, array $context = [])
	{
		call_user_func_array([self::getFacadeRoot(), __FUNCTION__], [$message, $context]);
	}

	/**
	 * 出现非错误性的异常。
	 * 例如：使用了被弃用的API、错误地使用了API或者非预想的不必要错误。
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return ILog
	 */
	public static function Warning($message, array $context = [])
	{
		call_user_func_array([self::getFacadeRoot(), __FUNCTION__], [$message, $context]);
	}

	/**
	 * 一般性重要的事件。
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return ILog
	 */
	public static function Notice($message, array $context = [])
	{
		call_user_func_array([self::getFacadeRoot(), __FUNCTION__], [$message, $context]);
	}

	/**
	 * 重要事件
	 * 例如：用户登录和SQL记录。
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return ILog
	 */
	public static function Info($message, array $context = [])
	{
		call_user_func_array([self::getFacadeRoot(), __FUNCTION__], [$message, $context]);
	}

	/**
	 * debug 详情
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return ILog
	 */
	public static function Debug($message, array $context = [])
	{
		call_user_func_array([self::getFacadeRoot(), __FUNCTION__], [$message, $context]);
	}
}