<?php

namespace DIServer;

/**
 * 自动生成目录
 *
 * @author Back
 */
class Build
{

    // 检测应用目录是否需要自动创建
    static public function checkDir($serverName)
    {
	if (!is_dir(DI_APP_PATH . '/' . $serverName))
	{
	    // 创建模块的目录结构
	    Build::buildAppServerDir($serverName);
	    die("$serverName is ready, please finish the base setting in " . DI_APP_SERVER_CONF_PATH . '/Config' . CONF_EXT . " and restart the server.\n");
	}
    }

    // 创建应用和模块的目录结构
    static private function buildAppServerDir($serverName)
    {
	// 没有创建的话自动创建
	if (!is_dir(DI_APP_PATH))
	    mkdir(DI_APP_PATH, 0755, true);
	if (is_writeable(DI_APP_PATH))
	{
	    Build::makeDirs(); //生成基础目录
	    Build::makeConfigs(); //生成基础配置文件
	    Build::makeFunctions(); //生成基础函数文件
	    Build::makeReloadHelper($serverName); //生成基础热重载类。
	}
	else
	{
	    exit("应用目录[' . DI_APP_PATH . ']不可写，目录无法自动生成！\n请手动生成项目目录~");
	}
    }

    static private function makeReloadHelper($serverName)
    {
	//生成ReloadHelper
	$reloadHelperFilePath = DI_APP_SERVER_WORKER_PATH . '/ReloadHelper.php';
	if (!is_file($reloadHelperFilePath))
	    file_put_contents($reloadHelperFilePath, "<?php\n"
		    . "\n"
		    . "namespace {$serverName}\Worker;\n"
		    . "\n"
		    . "use DIServer\BaseReloadHelper;\n"
		    . "/*\n"
		    . " *WGServer的热重启助手类，可以自行覆写父类的方法（操作不当会导致框架无法正常运行，请仔细阅读父类的注释再修改）\n"
		    . " */\n"
		    . "class ReloadHelper extends BaseReloadHelper\n"
		    . "{\n\t"
		    . "/*\n\t "
		    . " *需要覆写的方法\n\t "
		    . " */\n"
		    . "}");
    }

    static private function makeFunctions()
    {
	//生成Server方法文件
	if (!is_file(DI_APP_SERVER_COMMON_PATH . '/DI_Function.php'))
	    file_put_contents(DI_APP_SERVER_COMMON_PATH . '/DI_Function.php', "<?php\n"
		    . "/*\n"
		    . " *这里的方法不会被热重载，仅在服务被加载的时候生效。请将与Server有关的方法放在这里\n"
		    . " *此目录下文件名以Function.php结尾的文件都会被加载，可以根据需要拆分文件。\n"
		    . " */");
	//生成Worker方法文件
	if (!is_file(DI_APP_SERVER_WORKER_COMMON_PATH . '/DI_Function.php'))
	    file_put_contents(DI_APP_SERVER_WORKER_COMMON_PATH . '/DI_Function.php', "<?php\n"
		    . "/*\n"
		    . " *这里的方法会在ServerReload的时候被热重载\n请将与业务有关的方法放在这里\n"
		    . " *此目录下文件名以Function.php结尾的文件都会被加载，可以根据需要拆分文件。\n"
		    . " */");
    }

    static private function makeConfigs()
    {
	// 写入Server配置文件
	if (!is_file(DI_APP_SERVER_CONF_PATH . '/DI_Config' . CONF_EXT))
	    file_put_contents(DI_APP_SERVER_CONF_PATH . '/DI_Config' . CONF_EXT, '.php' == CONF_EXT ?
			    "<?php\n"
			    . "//这里的配置不会被热重启，仅在服务被加载的时候生效\n"
			    . "//请将与Server有关的配置放在这里\n"
			    . "//该目录下文件名以Config." . CONF_EXT . "为结尾的文件都会被看作配置被加载\n"
			    . "//可以根据需要拆分文件。\n"
			    . "return [\n\t//'配置项'=>'配置值'\n\t"
			    . "'DI_LISTENERS' => [\n\t\t"
			    . "['Host' => '0.0.0.0', 'Port' => '5200', 'Type' => SWOOLE_SOCK_UDP]\n\t"
			    . "]//其他配置项请参考惯例配置文件\n"
			    . "];" : '');
	// 写入Worker配置文件
	if (!is_file(DI_APP_SERVER_WORKER_CONFIG_PATH . '/DI_Config' . CONF_EXT))
	    file_put_contents(DI_APP_SERVER_WORKER_CONFIG_PATH . '/DI_Config' . CONF_EXT, '.php' == CONF_EXT ?
			    "<?php\n"
			    . "//这里的配置会在ServerReload的时候被热重载\n"
			    . "//请将与业务有关的配置放在这里\n"
			    . "//该目录下文件名以Config." . CONF_EXT . "为结尾的文件都会被看作配置被加载\n"
			    . "//可以根据需要拆分文件。\n"
			    . "return [\n\t"
			    . "//'配置项'=>'配置值'\n"
			    . "];" : '');
    }

    static private function makeDirs()
    {
	$dirs = array(
	    //Common目录
	    DI_APP_COMMON_PATH, //公共配置目录
	    DI_APP_COMMON_HANDLER_PATH, //公共Handler目录
	    DI_APP_COMMON_REQUEST_PATH, //公共Request目录
	    DI_APP_COMMON_TICKER_PATH, //公共Ticker目录	    
	    //Server目录
	    DI_APP_SERVER_PATH, //Server目录
	    DI_APP_SERVER_COMMON_PATH, //Server普通function目录
	    DI_APP_SERVER_CONF_PATH, //Server普通Conf目录
	    DI_APP_SERVER_HANDLER_PATH, //Server的Handler目录
	    DI_APP_SERVER_REQUEST_PATH, //Server的Request目录
	    DI_APP_SERVER_TICKER_PATH, //Server的Ticker目录
	    DI_APP_SERVER_SERVICE_PATH, //Server的Service目录
	    DI_APP_SERVER_PATH . '/Temp', //swoole服务的临时文件目录
	    DI_LOG_PATH, //Server的日志目录
	    //Worker目录
	    DI_APP_SERVER_WORKER_PATH, //worker目录
	    DI_APP_SERVER_WORKER_COMMON_PATH, //Server的worker级function目录
	    DI_APP_SERVER_WORKER_CONFIG_PATH//Server的worker级conf目录
	);
	foreach ($dirs as $dir)
	{
	    if (!is_dir($dir))
		mkdir($dir, 0755, true);
	}
    }

}
