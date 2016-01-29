<?php

namespace DIServer\Services;

use DIServer\Interfaces\IMonitor;

class Monitor extends Facade
{
	public static function getFacadeAccessor()
	{
		return IMonitor::class;
	}

	public static function All()
	{
		/** @var IMonitor $instance */
		$instance = self::getFacadeRoot();

		return call_user_func_array([$instance, __FUNCTION__], []);
	}

	public static function OnTaskSend($workerID, $taskWorkerID)
	{
		/** @var IMonitor $instance */
		$instance = self::getFacadeRoot();

		return call_user_func_array([$instance, __FUNCTION__], [$workerID, $taskWorkerID]);
	}

	public static function OnTaskReceive($taskWorkerID)
	{
		/** @var IMonitor $instance */
		$instance = self::getFacadeRoot();

		return call_user_func_array([$instance, __FUNCTION__], [$taskWorkerID]);
	}

	public static function OnTaskFinish($taskWorkerID)
	{
		/** @var IMonitor $instance */
		$instance = self::getFacadeRoot();

		return call_user_func_array([$instance, __FUNCTION__], [$taskWorkerID]);
	}

	public static function GetWorkerSendCount($workerID)
	{
		/** @var IMonitor $instance */
		$instance = self::getFacadeRoot();

		return call_user_func_array([$instance, __FUNCTION__], [$workerID]);
	}

	public static function GetTaskReceiveCount($taskWorkerID)
	{
		/** @var IMonitor $instance */
		$instance = self::getFacadeRoot();

		return call_user_func_array([$instance, __FUNCTION__], [$taskWorkerID]);
	}

	public static function GetTaskFinishCount($taskWorkerID)
	{
		/** @var IMonitor $instance */
		$instance = self::getFacadeRoot();

		return call_user_func_array([$instance, __FUNCTION__], [$taskWorkerID]);
	}

	public static function GetTaskFailedCount($taskWorkerID)
	{
		/** @var IMonitor $instance */
		$instance = self::getFacadeRoot();

		return call_user_func_array([$instance, __FUNCTION__], [$taskWorkerID]);
	}
}