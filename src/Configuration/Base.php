<?php

namespace DIServer\Configuration;

use DIServer\Helpers\Ary;
use DIServer\Helpers\IO;
use DIServer\Interfaces\IConfig;
use DIServer\Services\Application;
use DIServer\Services\Event;
use DIServer\Services\Log;

class Base implements IConfig
{
	protected $config = [];
	protected $cache = [];

	protected function PathAnalyze($path, $default = null, $split = '.')
	{
		$keys = explode($split, $path);
		$pk = array_shift($keys);//理论上应该是配置文件名（无后缀）
		$res = $this->config[$pk];
		if(!$res)
		{
			$this->autoLoad($pk);//尝试自动搜索目录加载文件
			$res = $this->config[$pk];//尝试重新获取
		}

		foreach($keys as $key)
		{
			if(is_array($res))
			{
				$res = $res[$key];
			}
		}
		$this->cache[$path] = $res === null ? $default : $res;

		return $res;
	}


	/**
	 * 在第一次调用时动态加载配置文件
	 *
	 * @param $pk
	 */
	protected function autoLoad($pk, $ext = '.php')
	{
		$files = Application::GetConventionPaths("/Config/{$pk}{$ext}");
		foreach($files as $file)
		{
			Log::Debug($file);
			$this->loadFromArray($file, $ext);
		}
	}

	protected function loadFromArray($filePath, $ext = '.php')
	{
		if(file_exists($filePath))
		{
			$key = basename($filePath, $ext);
			$newConfigs[$key] = include $filePath;
			Ary::MergeRecursive($this->config, $newConfigs);
		}
	}


	/**
	 * 加载指定目录下的配置并生成[绝对路径=>配置]
	 * (备用）
	 *
	 * @param        $filePath
	 * @param string $ext
	 */
	private function LoadV2($filePath, $ext = '.php')
	{
		$resConfig = [];
		$split = '.';
		if(file_exists($filePath))
		{
			$key = basename($filePath, $ext);
			$configs = include $filePath;
			$keyStack = new \SplStack();
			$keyStack->push([$key, $configs]);

			$whileCount = 0;//防止意外进入死循环，限制最多循环1024层
			while(!$keyStack->isEmpty() && $whileCount < 1024)
			{
				$whileCount++;
				$pair = $keyStack->pop();
				foreach($pair[1] as $pairKey => $pairVal)
				{
					if(is_array($pairVal))
					{
						$keyStack->push([$pair[0] . $split . $pairKey, $pairVal]);
					}
					else
					{
						$resConfig[$pair[0] . $split . $pairKey] = $pairVal;
					}
				}
			}
		}

		return $resConfig;
	}

	/**
	 * 检查是否存在指定的配置项
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function Has($key)
	{
		if(isset($this->cache[$key]))
		{
			return true;
		}
		else
		{
			return $this->Get($key) === null ? false : true;
		}
	}

	/**
	 * 获取指定的配置项
	 *
	 * @param      $key
	 * @param null $default
	 *
	 * @return mixed
	 */
	public function Get($key, $default = null)
	{
		return isset($this->cache[$key]) ? $this->cache[$key] : $this->PathAnalyze($key, $default);
	}

	/**
	 * 获取所有的配置
	 *
	 * @return array
	 */
	public function All()
	{
		return $this->config;
	}

	/**
	 * 设置指定的配置项
	 *
	 * @param      $key
	 * @param null $value
	 *
	 * @return mixed
	 */
	public function Set($key, $value = null)
	{
		$this->cache[$key] = $value;
	}

	/**
	 * 向指定配置末端添加一个子项
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public function Push($key, $value)
	{
		if(is_array($this->cache[$key]))
		{
			$this->cache[$key][] = $value;
		}
		else
		{
			$old = $this->cache[$key];
			$this->cache[$key][] = [$old, $value];
		}
	}

	/**
	 * 向指定配置项第一个位置插入一个子项
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public function Prepend($key, $value)
	{
		if(is_array($this->cache[$key]))
		{
			array_unshift($this->cache[$key], $value);
		}
		else
		{
			$old = $this->cache[$key];
			$this->cache[$key][] = [$value, $old];
		}
	}

	/**
	 * Whether a offset exists
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset <p>
	 *                      An offset to check for.
	 *                      </p>
	 *
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 * @since 5.0.0
	 */
	public function offsetExists($offset)
	{
		return isset($this->cache[$offset]);
	}

	/**
	 * Offset to retrieve
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to retrieve.
	 *                      </p>
	 *
	 * @return mixed Can return all value types.
	 * @since 5.0.0
	 */
	public function offsetGet($offset)
	{
		return $this->cache[$offset];
	}

	/**
	 * Offset to set
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to assign the value to.
	 *                      </p>
	 * @param mixed $value  <p>
	 *                      The value to set.
	 *                      </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetSet($offset, $value)
	{
		$this->cache[$offset] = $value;
	}

	/**
	 * Offset to unset
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to unset.
	 *                      </p>
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetUnset($offset)
	{
		unset($this->cache[$offset]);
	}
}