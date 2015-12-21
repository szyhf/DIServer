<?php

namespace DIServer\Lib\DI;

use DIServer\Lib\DI\DIContainer\DIContainer;

/**
 * 自动注册机（用于注册指定目录下的文件类）
 * @author Back
 */
class AutoRegister
{

	/**
	 * 默认容器
	 * @var DIServer\Lib\DI\DIContainer\DIContainer
	 */
	private $container;

	public function __construct(DIContainer $container)
	{
		$this->container = $container;
	}

	/**
	 * 自动向默认容器注册指定目录下的类
	 *
	 * @param $path 指定要注册的目录
	 */
	public function Register($directory = __DIR__)
	{
		$files = $this->AllFile($directory, FALSE, '.php');
		foreach ($files as $file)
		{
			$fileName = array_pop(explode('/', $file));

			//根据目录推测命名空间
			$namespace = ltrim($file, dirname(DI_DISERVER_PATH));
			$namespace = rtrim($namespace, $fileName);
			$namespace = str_replace(DIRECTORY_SEPARATOR, '\\', $namespace);

			$sortClassName = str_ireplace('.php', '', $fileName);
			$className = $namespace . $sortClassName;
			echo "$className\n";
			//$this->container->RegisterClass($className);
		}
	}

	/**
	 * 获取指定路径下的所有文件列表（可递归）
	 *
	 * @param type $directory 路径
	 * @param type $recu      是否递归获取子目录
	 * @param type $ext       指定文件名结尾字符串（例如，扩展名）
	 *
	 * @return array 所有的文件完整路径
	 */
	private function AllFile($directory = __DIR__, $recu = FALSE, $ext = '')
	{
		$mydir = dir($directory);
		if (!$mydir)
		{
			DILog("$directory is not exist or available.", 'w');

			return [];
		}
		$files = [];
		$dirs = [];
		if (empty($ext))
		{
			while ($file = $mydir->read())
			{
				if (($file == ".") OR ($file == ".."))
				{
					continue;
				}
				else if ((is_dir("$directory/$file")))
				{
					if ($recu)//递归，为了确保子目录内的文件在父目录之后才被加载，先缓存子目录路径
					{
						$dirs[] = "$directory/$file";
					}
				}
				else
				{
					$files[] = $directory . '/' . $file;
				}
			}
		}
		else
		{
			while ($file = $mydir->read())
			{
				if (($file == ".") OR ($file == ".."))
				{
					continue;
				}
				else if ((is_dir("$directory/$file")))
				{
					if ($recu)//递归，为了确保子目录内的文件在父目录之后才被加载，先缓存子目录路径
					{
						$dirs[] = "$directory/$file";
						//		    $files = array_merge(AllFile("$directory/$file", $recu, $ext), $files);
					}
				}
				else if (preg_match("/" . $ext . '$/', $file))
				{
					$files[] = $directory . '/' . $file;
				}
			}
		}
		$mydir->close();
		natsort($files);
		foreach ($dirs as $dir)
		{
			$files = array_merge($files, AllFile($dir, $recu, $ext));
		}

		return $files;
	}

}
