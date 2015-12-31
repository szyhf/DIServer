<?php
/**
 * Created by PhpStorm.
 * User: Back
 * Date: 2015/12/30
 * Time: 14:22
 */

namespace DIServer\Session\Driver;

use DIServer\Interfaces\IApplication;
use DIServer\Session\Session;
use Symfony\Component\Finder\Finder;

class File extends Session
{
	/**
	 * @var
	 */
	protected $fileIO;

	/**
	 * 会话存储路径
	 *
	 * @var string
	 */
	protected $path;

	public function __construct(IApplication $app)
	{
		parent::__construct($app);
		$this->path = $app->GetServerPath() . '/Runtimes/Session';
		if(!is_dir($this->path))
		{
			//echo "mkdir($this->path, 0755, true);\n";
			mkdir($this->path, 0755, true);
		}
	}

	/**
	 * Close the session
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.close.php
	 * @return bool <p>
	 *        The return value (usually TRUE on success, FALSE on failure).
	 *        Note this value is returned internally to PHP for processing.
	 *        </p>
	 * @since 5.4.0
	 */
	public function close()
	{
		// TODO: Implement close() method.
		return true;
	}

	/**
	 * Destroy a session
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.destroy.php
	 *
	 * @param string $session_id The session ID being destroyed.
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function destroy($session_id)
	{
		// TODO: Implement destroy() method.
		echo "Destory $session_id";
		//
		//return true;

		return unlink($this->path . "/$session_id");
	}

	/**
	 * Cleanup old sessions
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.gc.php
	 *
	 * @param int $maxlifetime <p>
	 *                         Sessions that have not updated for
	 *                         the last maxlifetime seconds will be removed.
	 *                         </p>
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function gc($maxlifetime)
	{
		// TODO: Implement gc() method.
		$files = Finder::create()
		               ->in($this->path)
		               ->files()
		               ->ignoreDotFiles(true)
		               ->date('<=now - ' . $maxlifetime . ' seconds');

		/** @var \Symfony\Component\Finder\SplFileInfo $file */
		foreach($files as $filePath => $fileInfo)
		{
			//echo "gc file:$filePath\n";
			//var_dump($file);
			unlink($filePath);
		}

		return true;
	}

	/**
	 * Initialize session
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.open.php
	 *
	 * @param string $save_path  The path where to store/retrieve the session.
	 * @param string $session_id The session id.
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function open($save_path, $session_id)
	{
		// TODO: Implement open() method.


		return true;
	}

	/**
	 * Read session data
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.read.php
	 *
	 * @param string $session_id The session id to read data for.
	 *
	 * @return string <p>
	 * Returns an encoded string of the read data.
	 * If nothing was read, it must return an empty string.
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function read($session_id)
	{
		// TODO: Implement read() method.
		echo "Session read $this->path/$session_id\n";
		if(file_exists($this->path . "/$session_id"))
		{
			return file_get_contents($this->path . "/$session_id");
		}


		return '';
	}

	/**
	 * Write session data
	 *
	 * @link  http://php.net/manual/en/sessionhandlerinterface.write.php
	 *
	 * @param string $session_id   The session id.
	 * @param string $session_data <p>
	 *                             The encoded session data. This data is the
	 *                             result of the PHP internally encoding
	 *                             the $_SESSION superglobal to a serialized
	 *                             string and passing it as this parameter.
	 *                             Please note sessions use an alternative serialization method.
	 *                             </p>
	 *
	 * @return bool <p>
	 * The return value (usually TRUE on success, FALSE on failure).
	 * Note this value is returned internally to PHP for processing.
	 * </p>
	 * @since 5.4.0
	 */
	public function write($session_id, $session_data)
	{
		// TODO: Implement write() method.
		echo "Session write $this->path/$session_id >> $session_data\n";
		return file_put_contents($this->path . "/$session_id", $session_data, LOCK_EX);
	}
}