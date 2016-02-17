<?php
namespace DIServer\Helpers
{
	class IO
	{
		/**
		 * 获取指定路径下的所有文件列表（可递归）
		 *
		 * @param type $directory 路径
		 * @param type $recu      是否递归获取子目录
		 * @param type $ext       指定文件名结尾字符串（例如，扩展名）
		 *
		 * @return array 所有的文件完整路径
		 */
		public static function AllFile($directory = __DIR__, $recu = false, $ext = '')
		{
			$mydir = dir($directory);
			if(!$mydir)
			{
				DILog("$directory is not exist or available.", 'w');

				return [];
			}
			$files = [];
			$dirs = [];
			if(empty($ext))
			{
				while($file = $mydir->read())
				{
					if(($file == ".") OR ($file == ".."))
					{
						continue;
					}
					else
					{
						if((is_dir("$directory/$file")))
						{
							if($recu)//递归，为了确保子目录内的文件在父目录之后才被加载，先缓存子目录路径
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
			}
			else
			{
				while($file = $mydir->read())
				{
					if(($file == ".") OR ($file == ".."))
					{
						continue;
					}
					else
					{
						if((is_dir("$directory/$file")))
						{
							if($recu)//递归，为了确保子目录内的文件在父目录之后才被加载，先缓存子目录路径
							{
								$dirs[] = "$directory/$file";
								//		    $files = array_merge(AllFile("$directory/$file", $recu, $ext), $files);
							}
						}
						else
						{
							if(preg_match("/" . $ext . '$/', $file))
							{
								$files[] = $directory . '/' . $file;
							}
						}
					}
				}
			}
			$mydir->close();
			natsort($files);
			foreach($dirs as $dir)
			{
				$files = array_merge($files, AllFile($dir, $recu, $ext));
			}

			return $files;
		}
	}
}