<?php
namespace DIServer\Bootstraps;

/**
 * 自动创建应用基础目录
 *
 * @package DIServer\Bootstraps
 */
class AutoBuilder extends Bootstrap
{
	public function Bootstrap()
	{
		$dirs = include Application::GetFrameworkPath() . '/Config/Directory.php';
		foreach($dirs as $dir)
		{
			$this->tryBuild($dir);
		}
	}

	private function tryBuild($path)
	{
		if(!is_dir($path))
		{
			if(!mkdir($path, 0755, true))
			{
				throw new BootException("Can't not auto build application directories.");
			}
			var_dump($path);
		}
	}
}