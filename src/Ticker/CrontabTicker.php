<?php

namespace DIServer\Ticker;


use DIServer\Services\Log;

class CrontabTicker
{
	private $_now = 0;//用户提供的起点
	private $_start = 0;//统计起点
	/**
	 * 将_start拆分为各个周期的集合
	 *
	 * @var array
	 */
	private $_periods = [];
	private $_nextPeriods = [];
	private $_limits = [];
	private $_crontab = '';
	private $_availableTimes = [];
	private $_log = [];
	/** @var \Iterator 迭代器 */
	private $_nextIterator = null;
	/** @var int 下次回调时间 */
	private $_nextTime = 0;
	const YEAR = 'Y';
	const MONTH = 'n';
	const WEEK = 'w';
	const DAY = 'j';
	const HOUR = 'G';
	const MINUTE = 'i';
	const SECOND = 's';
	const SHORT_MAP = [
		'sun'       => 0,
		'sunday'    => 0,
		'mon'       => 1,
		'monday'    => 1,
		'tues'      => 2,
		'tue'       => 2,
		'tuesday'   => 2,
		'wed'       => 3,
		'wednesday' => 3,
		'thur'      => 4,
		'thu'       => 4,
		'thursday'  => 4,
		'fri'       => 5,
		'friday'    => 5,
		'sat'       => 6,
		'saturday'  => 6,
		'jan'       => 1,
		'january'   => 1,
		'feb'       => 2,
		'february'  => 2,
		'mar'       => 3,
		'march'     => 4,
		'apr'       => 4,
		'april'     => 4,
		'may'       => 5,
		'jun'       => 6,
		'june'      => 6,
		'jul'       => 7,
		'july'      => 7,
		'aug'       => 8,
		'august'    => 8,
		'sep'       => 9,
		'sept'      => 9,
		'september' => 9,
		'oct'       => 10,
		'october'   => 10,
		'nov'       => 11,
		'november'  => 11,
		'dec'       => 12,
		'december'  => 12
	];

	/**
	 * 设定Crontab string
	 *
	 * @param $crontabString :
	 *                       0     1    2    3    4    5
	 *                       *     *    *    *    *    *
	 *                       -     -    -    -    -    -
	 *                       |     |    |    |    |    |
	 *                       |     |    |    |    |    +----- day of week (0 - 6) (Sunday=0)
	 *                       |     |    |    |    +----- month (1 - 12)
	 *                       |     |    |    +------- day of month (1 - 31)
	 *                       |     |    +--------- hour (0 - 23)
	 *                       |     +----------- min (0 - 59)
	 *                       +------------- sec (0-59)
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function Crontab($crontabString)
	{
		if(!is_string($crontabString))
		{
			throw new \Exception("\$crontabString should be a string.");
		}


		//处理空白字符
		$crontabString = trim($crontabString);

		//如果包含字母，考虑使用英文单词描述周或者月的情况
		if(preg_match('/[A-Za-z]*/', $crontabString))
		{
			$crontabString = strtolower($crontabString);//处理成小写
			foreach(self::SHORT_MAP as $str => $vol)
			{
				$crontabString = str_replace($str, $vol, $crontabString);
			}
		}

		//检查crontab是否符合规范
		if(!preg_match('/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i',
		               $crontabString)
		)
		{
			if(!preg_match('/^((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)\s+((\*(\/[0-9]+)?)|[0-9\-\,\/]+)$/i',
			               $crontabString)
			)
			{
				throw new \Exception("Invalid crontab string: " . $crontabString);
			}
		}
		$this->_clear();
		$this->_crontab = $crontabString;

		return $this;
	}

	/**
	 * 设定统计时间的起点，如果不设置则默认从time()获取
	 *
	 * @param int $time
	 *
	 * @return $this
	 */
	public function From($time)
	{
		$this->_now = $time;

		return $this;
	}

	/**
	 * 下个触发时间的时间戳
	 *
	 * @return \Iterator
	 */
	public function Next()
	{
		$this->_initLimits();//初始化限制条件
		//Log::Debug($this->_availableTimes);
		//$this->_initStart();//初始化统计起点
		//$this->_initPeriods();//初始化可用周期
		//yield $this->_nextAvailableTime();
		while($this->_now < PHP_INT_MAX)
		{
			$this->_initStart();//初始化统计起点
			$this->_initPeriods();//初始化可用周期
			$this->_now = $this->_nextAvailableTime();
			yield $this->_now++;
			//Log::Debug("yield:{0}", [self::FormatTime($this->_now)]);
		}
	}

	/**
	 * 定时器触发时执行的回调
	 *
	 * @param callable $callback function($tickID,$params=null){}
	 * @param mixed    $params   希望传入回调函数的参数
	 *
	 * @return $this
	 */
	public function Then(callable $callback, $params = null)
	{
		$this->_nextIterator = self::Next();
		$this->_nextTime = $this->_nextIterator->current();
		Log::Debug($this->FormatTime($this->_nextTime));
		$tickFunc = function ($called = false) use (&$tickFunc, &$callback, &$params)
		{
			$maxCallbackLimits = 30;
			if($called)
			{
				Log::Debug("Tick callback called.");
				//如果called为true，说明是差距回调，先执行用户方法
				if(call_user_func($callback, $params) === false)
				{
					Log::Debug('Return false.');

					//如果用户回调函数返回false，则停止当然日程继续回调
					return;
				}
			}

			while($this->_nextTime <= time())
			{
				//已知时间超时，则更新已知时间
				$this->_nextIterator->next();
				$this->_nextTime = $this->_nextIterator->current();
				Log::Debug("Next time update to {0}", [$this->FormatTime($this->_nextTime)]);
			}

			$timeAfter = $this->_nextTime - time();
			if($timeAfter > $maxCallbackLimits)//timeTick最高支持86400s
			{
				Log::Debug("$timeAfter>$maxCallbackLimits, next reset call at " .
				           $this->FormatTime(time() + $timeAfter));

				//下次回调在一天以后，则设置一天后重新统计剩余时间
				return swoole_timer_after($maxCallbackLimits * 1000, $tickFunc);
			}
			else
			{
				Log::Debug("$timeAfter<=$maxCallbackLimits, will call at " . $this->FormatTime($this->_nextTime));

				return swoole_timer_after($timeAfter * 1000, $tickFunc, true);
			}
		};

		return $tickFunc(false);
	}

	/**
	 * 下次触发距离设定时间的间隔（秒）
	 *
	 * @return int
	 */
	public function Till()
	{
		$this->_initLimits();//初始化限制条件
		$this->_initStart();//初始化统计起点
		$this->_initPeriods();//初始化可用周期
		return $this->_nextAvailableTime() - $this->_now;
	}

	private function _clear()
	{
		$this->_start = 0;
		$this->_periods = [];
		$this->_nextPeriods = [];
		$this->_limits = [];
		$this->_parse = '';
		$this->_availableTimes = [];
		$this->_log = [];
		$this->_nextTime = 0;
		$this->_nextIterator = null;
	}

	/**
	 * @param $s
	 * @param $min
	 * @param $max
	 *
	 * @return array
	 */
	private function _parseCrontabNumber($s, $min, $max)
	{
		$result = [];
		$v1 = explode(",", $s);
		foreach($v1 as $v2)
		{
			$v3 = explode("/", $v2);
			$step = empty($v3[1]) ? 1 : $v3[1];
			$v4 = explode("-", $v3[0]);
			if(count($v4) == 2)//涉及分阶段的计算
			{
				if($v4[0] <= $v4[1])
				{
					$_min = $v4[0];
					$_max = $v4[1];
				}
				else//形如22-7实际表示22-23,0-7
				{
					//即$v4[0]-$max/$step,$min-$v4[1]/$step，重新拼接字符串计算
					$s = "{$v4[0]}-$max/$step,$min-{$v4[1]}/$step";

					return $this->_parseCrontabNumber($s, $min, $max);
				}
			}
			else
			{
				$_min = ($v3[0] == "*" ? $min : $v3[0]);
				$_max = ($v3[0] == "*" ? $max : $v3[0]);
			}
			for($i = $_min; $i <= $_max; $i += $step)
			{
				$result[$i] = intval($i);
			}
		}
		ksort($result);

		return $result;
	}

	/**
	 * 初始化生效的约束（在更高一级的约束存在时，将低级约束的默认‘*’处理成min(低级约束）的方案）
	 * 如 * 1 * * * * 处理为0 1 * * * *
	 */
	private function _initLimits_bak()
	{
		$cron = preg_split("/[\s]+/i", trim($this->_crontab));
		if(count($cron) == 5)
		{
			//5个参数的时候自动补秒约束0
			array_unshift($cron, '0');
		}

		$this->_availableTimes[self::MONTH] = self::_parseCrontabNumber($cron[4], 1, 12);
		if($cron[4] != '*')//Month
		{
			$this->_limits[self::MONTH] = true;
			$this->_limits[self::DAY] = true;
			$this->_limits[self::HOUR] = true;
			$this->_limits[self::MINUTE] = true;
			$this->_limits[self::SECOND] = true;
		}

		$this->_availableTimes[self::WEEK] = self::_parseCrontabNumber($cron[5], 0, 6);
		if($cron[5] != '*')//Week
		{
			$this->_limits[self::WEEK] = true;
			$this->_limits[self::HOUR] = true;
			$this->_limits[self::MINUTE] = true;
			$this->_limits[self::SECOND] = true;
		}

		$this->_availableTimes[self::DAY] = self::_parseCrontabNumber($cron[3], 1, 31);
		if($cron[3] != '*')//Day
		{
			$this->_limits[self::DAY] = true;
			$this->_limits[self::HOUR] = true;
			$this->_limits[self::MINUTE] = true;
			$this->_limits[self::SECOND] = true;
		}

		if($cron[2] != '*')//Hour
		{
			$this->_availableTimes[self::HOUR] = self::_parseCrontabNumber($cron[2], 0, 23);
			$this->_limits[self::HOUR] = true;
			$this->_limits[self::MINUTE] = true;
			$this->_limits[self::SECOND] = true;
		}
		elseif($this->_isLimited(self::HOUR))
		{
			$this->_availableTimes[self::HOUR] = [0 => 0];
		}

		if($cron[1] != '*')//Minute
		{
			$this->_availableTimes[self::MINUTE] = self::_parseCrontabNumber($cron[1], 0, 59);
			$this->_limits[self::MINUTE] = true;
			$this->_limits[self::SECOND] = true;
		}
		elseif($this->_isLimited(self::MINUTE))
		{
			$this->_availableTimes[self::MINUTE] = [0 => 0];
		}

		if($cron[0] != '*')//Second
		{
			$this->_availableTimes[self::SECOND] = self::_parseCrontabNumber($cron[0], 0, 59);
			$this->_limits[self::SECOND] = true;
		}
		elseif($this->_isLimited(self::SECOND))
		{
			$this->_availableTimes[self::SECOND] = [0 => 0];
		}
	}

	/**
	 * Linux crontab风格的对*的处理
	 * 如 * 1 * * * * 处理为*\/1 1 * * * *
	 */
	private function _initLimits()
	{
		$cron = preg_split("/[\s]+/i", trim($this->_crontab));
		if(count($cron) == 5)
		{
			//5个参数的时候自动补秒约束0
			array_unshift($cron, '0');
		}
		$this->_availableTimes[self::MONTH] = self::_parseCrontabNumber($cron[4], 1, 12);
		$this->_availableTimes[self::WEEK] = self::_parseCrontabNumber($cron[5], 0, 6);
		$this->_availableTimes[self::DAY] = self::_parseCrontabNumber($cron[3], 1, 31);
		$this->_availableTimes[self::HOUR] = self::_parseCrontabNumber($cron[2], 0, 23);
		$this->_availableTimes[self::MINUTE] = self::_parseCrontabNumber($cron[1], 0, 59);
		$this->_availableTimes[self::SECOND] = self::_parseCrontabNumber($cron[0], 0, 59);

		if(count($this->_availableTimes[self::MONTH]) < 12)
		{
			$this->_limits[self::MONTH] = true;
		}
		if(count($this->_availableTimes[self::WEEK]) < 7)
		{
			$this->_limits[self::WEEK] = true;
		}
		if(count($this->_availableTimes[self::DAY]) < 31)
		{
			$this->_limits[self::DAY] = true;
		}
		if(count($this->_availableTimes[self::HOUR]) < 24)
		{
			$this->_limits[self::HOUR] = true;
		}
		if(count($this->_availableTimes[self::MINUTE]) < 60)
		{
			$this->_limits[self::MINUTE] = true;
		}
		if(count($this->_availableTimes[self::SECOND]) < 60)
		{
			$this->_limits[self::SECOND] = true;
		}
		if(count($this->_limits) == 0)
		{
			//如果没有任何一个约束，说明就是等价于* * * * * *的情况
			$this->_limits[self::SECOND] = true;
		}
	}

	/**
	 * 对start的各个周期属性进行分析并存储
	 */
	private function _initPeriods()
	{
		$this->_nextPeriods = [];//清理上一次遗留的数据
		$this->_periods[self::YEAR] = date(self::YEAR, $this->_start);
		$this->_periods[self::MONTH] = date(self::MONTH, $this->_start);
		$this->_periods[self::DAY] = date(self::DAY, $this->_start);
		$this->_periods[self::HOUR] = date(self::HOUR, $this->_start);
		$this->_periods[self::MINUTE] = date(self::MINUTE, $this->_start);
		$this->_periods[self::SECOND] = date(self::SECOND, $this->_start);
		$this->_periods[self::WEEK] = date(self::WEEK, $this->_start);
		$this->_nextPeriods[self::YEAR] = $this->_periods[self::YEAR];
		//$this->_nextPeriods = $this->_periods;
	}

	private function _initStart()
	{
		$this->_now = $this->_now ?: time();
		if($this->_hasEnoughTime())
		{
			$this->_start = $this->_now;
		}
		else
		{
			//今天剩余时间不足的话，直接以明天为起点进行统计
			$this->_start = $this->_getWeeTime($this->_now + 86400);
		}
	}

	/**
	 * 检查start时间所在的日中是否还有可能满足命令需求的时间点存在
	 */
	private function _hasEnoughTime()
	{
		$lastHour = $this->_isLimited(self::HOUR) ? max($this->_availableTimes[self::HOUR]) : 23;
		$lastMinute = $this->_isLimited(self::MINUTE) ? max($this->_availableTimes[self::MINUTE]) : 59;
		$lastSecond = $this->_isLimited(self::SECOND) ? max($this->_availableTimes[self::SECOND]) : 59;

		/** @var int $passedInStartDay 从当前时间的凌晨开始算起已经经过的时间 */
		$passedInStartDay = $this->_now - $this->_getWeeTime($this->_now);
		/** @var int $lastSeconds 理论上今天最晚可触发的时间点到凌晨的时间 */
		$lastSeconds = $lastHour * 3600 + $lastMinute * 60 + $lastSecond;

		return $passedInStartDay <= $lastSeconds;
	}

	/**
	 * 从可选的升序时间点中获得距离所需时间点最近的较大点（循环判定）
	 *
	 * @param array      $availableDates   可选的升序时间点
	 * @param int        $num              当前的时间点
	 * @param bool|false $nextPeriodOutput 是否进入了下一个循环周期
	 *
	 * @return bool|mixed
	 */
	private function _nextMatch(array $availableDates, $num, &$nextPeriodOutput = false)
	{
		$next = false;
		foreach($availableDates as $availableDate)
		{
			if($availableDate >= $num)
			{
				$next = $availableDate;
				break;
			}
		}
		$nextPeriodOutput = $next === false;//0是合法的
		$next = $nextPeriodOutput ? min($availableDates) : $next;

		return $next;
	}

	private function _countNextMonth()
	{
		$nextMonth = $this->_periods[self::MONTH];

		if($this->_isLimited(self::MONTH) || $this->_nextPeriods[self::MONTH])
		{
			$nextMonth = $this->_nextMatch($this->_availableTimes[self::MONTH],
			                               $this->_periods[self::MONTH] + $this->_nextPeriods[self::MONTH],
			                               $nextPeriod);
			//月溢出则增加一年
			$this->_nextPeriods[self::YEAR] += $nextPeriod ? 1 : 0;
		}
		$this->_nextPeriods[self::MONTH] = $nextMonth;
	}

	private function _countNextDay()
	{
		$nextDay = $this->_periods[self::DAY];
		if($this->_isLimited(self::DAY))
		{
			$nextDay = $this->_nextMatch($this->_availableTimes[self::DAY],
			                             $this->_periods[self::DAY],
			                             $nextPeriod);
			//当前月已无足够的日，可能的月溢出放在_countNextMonth中处理
			$this->_nextPeriods[self::MONTH] = $nextPeriod ? 1 : 0;
		}
		$this->_nextPeriods[self::DAY] = $nextDay;
	}

	private function _countNextHour()
	{
		$nextHour = $this->_periods[self::HOUR];
		if($this->_isLimited(self::HOUR) || $this->_nextPeriods[self::HOUR])
		{
			$nextHour = $this->_nextMatch($this->_availableTimes[self::HOUR],
			                              $this->_periods[self::HOUR] + $this->_nextPeriods[self::HOUR],
			                              $nextRound);
			if($nextRound)
			{
				//算法异常，因为已经验证过当天时间的充分性
				//如果还出现下个周期说明算法有问题。
				throw new \Exception("Algorithm error, next hour has overflow.");
			}

			if($nextHour != $this->_periods[self::HOUR])
			{
				//进入到新的Hour，则Minute和Second处理为可选的最小值。
				$this->_nextPeriods[self::MINUTE] = min($this->_availableTimes[self::MINUTE]);
				$this->_nextPeriods[self::SECOND] = min($this->_availableTimes[self::SECOND]);
			}
		}
		$this->_nextPeriods[self::HOUR] = $nextHour;
	}

	private function _countNextMinute()
	{
		$nextMinute = $this->_periods[self::MINUTE];
		if($this->_isLimited(self::MINUTE) || $this->_nextPeriods[self::MINUTE])
		{
			$nextMinute = $this->_nextMatch($this->_availableTimes[self::MINUTE],
			                                $this->_periods[self::MINUTE] + $this->_nextPeriods[self::MINUTE],
			                                $nextRound);

			$this->_nextPeriods[self::HOUR] = $nextRound ? 1 : 0;
			if($nextMinute != $this->_periods[self::MINUTE])
			{
				//进入新的Minute，则Second处理为可选的最小值。
				$this->_nextPeriods[self::SECOND] = min($this->_availableTimes[self::SECOND]);
			}
		}
		$this->_nextPeriods[self::MINUTE] = $nextMinute;
	}

	private function _countNextSecond()
	{
		$nextSecond = $this->_periods[self::SECOND];
		if($this->_isLimited(self::SECOND))
		{
			$nextSecond =
				$this->_nextMatch($this->_availableTimes[self::SECOND], $this->_periods[self::SECOND], $nextRound);
			//当前分钟内已无足够秒，进入下个可用分钟（可能导致小时不足进入下个小时，延后处理）
			$this->_nextPeriods[self::MINUTE] = $nextRound ? 1 : 0;
		}
		$this->_nextPeriods[self::SECOND] = $nextSecond;
	}

	/**
	 * 按week条件获取下一个可能的激活日
	 *
	 * @return bool|int int为下个可能激活日的0点0分0秒；false表示无week条件。
	 * @throws \Exception
	 */
	private function _nextWeekTime()
	{
		//默认根据当日凌晨零点截取时间
		$nextWeekTime = $this->_getWeeTime($this->_start);
		//存在week约束时
		if($this->_isLimited(self::WEEK))
		{
			$nextWeek = $this->_nextMatch($this->_availableTimes[self::WEEK],
			                              $this->_periods[self::WEEK]);
			//不在当天激活，则将时间调整为下个激活日的凌晨
			if($nextWeek != $this->_periods[self::WEEK])
			{
				$nextWeekName = $this->_getWeekName($nextWeek);
				$nextWeekTime = strtotime("next $nextWeekName", $this->_start);
			}
		}

		return $nextWeekTime;
	}

	/**
	 * 按照Day&Month的条件获取下个可能激活日的时间
	 */
	private function _nextDayTime()
	{
		$this->_countNextDay();
		$this->_countNextMonth();
		//根据月&&日的条件推算的最近触发日
		$nextDayTime = mktime(0,
		                      0,
		                      0,
		                      $this->_nextPeriods[self::MONTH],
		                      $this->_nextPeriods[self::DAY],
		                      $this->_nextPeriods[self::YEAR]);

		return $nextDayTime;
	}

	/**
	 * 获取下一个可用日的凌晨时间点
	 *
	 * @return int
	 */
	private function _nextAvailableDay()
	{
		/** @var bool $dayMonthLimit 日月约束 */
		$dayMonthLimit = $this->_isLimited(self::MONTH) || $this->_isLimited(self::DAY);
		/** @var bool $weekLimit 周约束 */
		$weekLimit = $this->_isLimited(self::WEEK);

		//如果既没有日月约束也没有周约束，则以$dayTime作为可行日（此时$dayTime==$weekTime）
		//如果只有日月约束，则以$dayTime作为可行日
		//如果只有周约束，则以$weekTime作为可行日
		//如果既有周约束又有日月约束，则以MIN($dayTime,$weekTime)作为可行日

		if(!$weekLimit)
		{
			return $this->_nextDayTime();
		}
		elseif(!$dayMonthLimit && $weekLimit)
		{
			$weekTime = $this->_nextWeekTime();
			//根据weekTime重置nextPeriods
			$this->_nextPeriods[self::YEAR] = date(self::YEAR, $weekTime);
			$this->_nextPeriods[self::MONTH] = date(self::MONTH, $weekTime);
			$this->_nextPeriods[self::DAY] = date(self::DAY, $weekTime);

			return $weekTime;
		}
		else
		{
			//既有
			$dayTime = $this->_nextDayTime();
			$weekTime = $this->_nextWeekTime();

			if($weekTime < $dayTime)
			{
				//根据weekTime重置nextPeriods
				$this->_nextPeriods[self::YEAR] = date(self::YEAR, $weekTime);
				$this->_nextPeriods[self::MONTH] = date(self::MONTH, $weekTime);
				$this->_nextPeriods[self::DAY] = date(self::DAY, $weekTime);

				return $weekTime;
			}
			else
			{
				return $dayTime;
			}
		}
	}

	/**
	 * 下个激活时间点的时间戳
	 *
	 * @return int
	 * @throws \Exception
	 */
	private function _nextAvailableTime()
	{
		$this->_nextAvailableDay();
		if($this->_isSameDay())
		{
			//按照秒、分、时的方式进行计算（以处理进位）
			$this->_countNextSecond();
			$this->_countNextMinute();
			$this->_countNextHour();
		}
		else
		{
			//不是同一天，直接获取可行的H:i:s中的最小时间点
			$this->_nextPeriods[self::HOUR] = min($this->_availableTimes[self::HOUR]);
			$this->_nextPeriods[self::MINUTE] = min($this->_availableTimes[self::MINUTE]);
			$this->_nextPeriods[self::SECOND] = min($this->_availableTimes[self::SECOND]);
		}

		return mktime($this->_nextPeriods[self::HOUR],
		              $this->_nextPeriods[self::MINUTE],
		              $this->_nextPeriods[self::SECOND],
		              $this->_nextPeriods[self::MONTH],
		              $this->_nextPeriods[self::DAY],
		              $this->_nextPeriods[self::YEAR]);
	}

	/**
	 * 工具：根据时间戳获取当天的凌晨0点0分0秒
	 *
	 * @param $time
	 *
	 * @return int
	 */
	private function _getWeeTime($time)
	{
		return strtotime(date('Ymd', $time));
	}

	/**
	 * 工具：获取周x的英文名称
	 *
	 * @param $week
	 *
	 * @return string
	 * @throws \Exception
	 */
	private function _getWeekName($week)
	{
		$weekName = '';
		switch($week)
		{
			case 0:
			{
				$weekName = 'Sunday';
				break;
			}
			case 1:
			{
				$weekName = 'Monday';
				break;
			}
			case 2:
			{
				$weekName = 'Tuesday';
				break;
			}
			case 3:
			{
				$weekName = 'Wednesday';
				break;
			}
			case 4:
			{
				$weekName = 'Thursday';
				break;
			}
			case 5:
			{
				$weekName = 'Friday';
				break;
			}
			case 6:
			{
				$weekName = 'Saturday';
				break;
			}
			default:
			{
				throw new \Exception("\$Week should between [0,6], $week given.");
			}
		}

		return $weekName;
	}

	/**
	 * 检测指定的周期是否被约束了
	 *
	 * @param $span
	 *
	 * @return bool
	 */
	private function _isLimited($span)
	{
		return isset($this->_limits[$span]);
	}

	/**
	 * 下个激活时间是否还在当天
	 *
	 * @return bool
	 */
	private function _isSameDay()
	{
		return $this->_periods[self::YEAR] == $this->_nextPeriods[self::YEAR] &&
		       $this->_periods[self::DAY] == $this->_nextPeriods[self::DAY] &&
		       $this->_periods[self::MONTH] == $this->_nextPeriods[self::MONTH];
	}

	/**
	 * 工具：格式化时间为字符串
	 *
	 * @param $time
	 *
	 * @return bool|string
	 */
	public function FormatTime($time)
	{
		return date('Y-m-d H:i:s w', $time);
	}

	public function DumpLog()
	{
		Log::Debug($this->_log);
	}
}