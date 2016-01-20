<?php

namespace DIServer\Session;


use DIServer\Interfaces\IApplication;
use DIServer\Interfaces\ISession;
use DIServer\Services\Service;

class Files extends Service implements ISession
{
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

	public function Write($sessionID, $data)
	{
		$data = serialize($data);

		return file_put_contents($this->path . "/$sessionID", $data, LOCK_EX);
	}

	public function Read($sessionID)
	{
		if(file_exists($this->path . "/$sessionID"))
		{
			return unserialize(file_get_contents($this->path . "/$sessionID"));
		}

		return [];
	}

	public function Destory($sessionID)
	{
		return unlink($this->path . "/$sessionID");
	}
}