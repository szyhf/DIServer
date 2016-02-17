<?php

namespace DIServer\Helpers;


class Ary
{
	/**
	 * 将第二个数组递归合并到第一个数组（与系统array_merge_recursive策略不同）
	 * 策略为：
	 * 如果子项都是数组，则继续根据子项内的Key进行合并；
	 * 如果有任一子项不是数组，则使用第二个数组的对应子项覆盖第一个数组的对应子项。
	 * （使用Stack实现）
	 *
	 * @param $ary1
	 * @param $ary2
	 * @param $recursiveMax 最大递归的层数（防止死循环）
	 */
	public static function MergeRecursive(&$ary1, $ary2, $recursiveMax = 256)
	{
		$keyStack = new \SplStack();
		$keyStack->push([&$ary1, $ary2]);

		$count = 0;
		while((!$keyStack->isEmpty()) && (++$count < $recursiveMax))
		{
			$pair = $keyStack->pop();
			foreach($pair[1] as $pairKey => $pairVal)
			{
				if(isset($pair[0][$pairKey]))
				{
					if(is_array($pair[0][$pairKey]) && is_array($pairVal))
					{
						$keyStack->push([&$pair[0][$pairKey], &$pairVal]);
						continue;
					}
				}
				$pair[0][$pairKey] = $pairVal;
			}
		}
	}

	/**
	 * 使用递归实现的MergeArrayRecursive（备用）
	 *
	 * @param     $old
	 * @param     $new
	 * @param int $recursiveCount
	 *
	 * @return mixed
	 */
	private static function MergeRecursiveV2(&$old, &$new, $recursiveCount = 0)
	{
		if(++$recursiveCount > 256)
		{
			return;
		}
		if(is_array($old) && is_array($new))
		{
			foreach($new as $key => $item)
			{
				if(isset($old[$key]))
				{
					self::MergeRecursiveV2($old[$key], $new[$key], $recursiveCount);
				}
				else
				{
					$old[$key] = $new[$key];
				}
			}
		}
		else
		{
			$old = $new;
		}
	}
}