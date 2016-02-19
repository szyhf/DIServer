<?php

namespace DIServer\Session;

use DIServer\Interfaces\ISession;
use DIServer\Services\Application;
use DIServer\Services\Log;
use DIServer\Services\Service;
use DIServer\Helpers\IO;

class File extends Service implements ISession
{
	protected $sessionID = null;
	protected $session = [];//当前Session服务

	/**
	 * 会话存储路径
	 *
	 * @var string
	 */
	protected $path;

	public function __construct()
	{
		$this->path = Application::GetServerPath('/Runtimes/Session');
		// 锁定
		$lockfile = $this->path . 'build.Runtimes.lock';
		if(is_writable($lockfile))
		{
			return;
		}
		else
		{
			if(!touch($lockfile))
			{
				throw new \Exception('应用目录[' . $this->path . ']不可写，目录无法自动生成！请手动生成项目目录~', 10006);
			}
		}
		if(!is_dir($this->path))
		{
			//echo "mkdir($this->path, 0755, true);\n";
			mkdir($this->path, 0755, true);
		}
		// 解除锁定
		unlink($lockfile);
	}

	public function Init()
	{
		//应该在OnMasterStart时调用，清空已有的Session
		$files = IO::AllFile($this->path);
		foreach($files as $file)
		{
			unlink($file);
		}
	}

	public function Reset()
	{
		unset($this->session);
	}

	public function Save()
	{
		$data = serialize($this->session);

		return file_put_contents($this->path . "/$this->sessionID", $data, LOCK_EX);
	}

	public function Load($sessionID)
	{
		$this->sessionID = $sessionID;
		if(file_exists($this->path . "/$this->sessionID"))
		{
			$this->session = unserialize(file_get_contents($this->path . "/$this->sessionID"));
		}
		else
		{
			$this->session = [];
		}
	}

	public function Destory()
	{
		return unlink($this->path . "/$this->sessionID");
	}

	public function GC()
	{
		//尝试进行回收
		$files = IO::AllFile($this->path);
		foreach($files as $file)
		{
			Log::Debug("$file =" . date('[Y-m-d H:i:s]', fileatime($file)));
			if(time() - fileatime($file) > 1440)//距离上次被访问超过1440秒之后清空Session
			{
				unlink($file);
			}
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
		return $this->Has($offset);
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
		return $this->Get($offset);
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
		$this->Set($offset, $value);
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
		unset($this->session[$offset]);
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
		return isset($this->session[$key]);
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
		return $this->session[$key];
	}

	/**
	 * 获取所有的配置
	 *
	 * @return array
	 */
	public function All()
	{
		return $this->session;
	}

	/**
	 * 设置指定的配置项
	 *
	 * @param      $key
	 * @param null $value
	 *
	 * @return mixed
	 */
	public function Set($key, $value)
	{
		return $this->session[$key] = $value;
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
		return $this->Set($key, $value);
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
		return $this->Set($key, $value);
	}
}