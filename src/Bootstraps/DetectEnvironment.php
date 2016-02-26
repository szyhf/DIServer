<?php

namespace DIServer\Bootstraps;

/**
 * 生成环境变量
 *
 * @author Back
 */
class DetectEnvironment extends Bootstrap
{

	public function Bootstrap()
	{
		$this->OldBoot();
	}

	public function OldBoot()
	{
		//defined('DI_DAEMONIZE') or define('DI_DAEMONIZE', 0);
		//defined('DI_CHECK_SERVER_DIR') or define('DI_CHECK_SERVER_DIR', 1);
		//
		////框架级
		//defined('DI_DISERVER_PATH') or define('DI_DISERVER_PATH', realpath(__DIR__ . '/../'));
		//defined('DI_COMMON_PATH') or define('DI_COMMON_PATH', DI_DISERVER_PATH . '/Common');
		//defined('DI_CONFIG_PATH') or define('DI_CONFIG_PATH', DI_DISERVER_PATH . '/Config');
		//defined('DI_REGISTRY_PATH') or define('DI_REGISTRY_PATH', DI_DISERVER_PATH . '/Registry');
		////defined('DI_REQUEST_PATH') or define('DI_REQUEST_PATH', DI_LIB_PATH . '/Request');
		////defined('DI_HANDLER_PATH') or define('DI_HANDLER_PATH', DI_LIB_PATH . '/Handler');
		////defined('DI_TICKER_PATH') or define('DI_TICKER_PATH', DI_LIB_PATH . '/Ticker');
		//
		////APP级
		//defined('DI_APP_PATH') or define('DI_APP_PATH', realpath(APP_PATH));
		//defined('DI_APP_COMMON_PATH') or define('DI_APP_COMMON_PATH', DI_APP_PATH . '/Common');
		//defined('DI_APP_COMMON_HANDLER_PATH') or define('DI_APP_COMMON_HANDLER_PATH', DI_APP_COMMON_PATH . '/Handler');
		//defined('DI_APP_COMMON_REQUEST_PATH') or define('DI_APP_COMMON_REQUEST_PATH', DI_APP_COMMON_PATH . '/Request');
		//defined('DI_APP_COMMON_TICKER_PATH') or define('DI_APP_COMMON_TICKER_PATH', DI_APP_COMMON_PATH . '/Ticker');
		//
		////Server级
		//defined('DI_APP_SERVER_PATH') or define('DI_APP_SERVER_PATH', DI_APP_PATH . '/' . DI_SERVER_NAME);
		////defined('DI_APP_SERVER_COMMON_PATH') or define('DI_APP_SERVER_COMMON_PATH', DI_APP_SERVER_PATH . '/Common');
		//defined('DI_APP_SERVER_CONFIG_PATH') or define('DI_APP_SERVER_CONFIG_PATH', DI_APP_SERVER_PATH . '/Config');
		////defined('DI_APP_SERVER_HANDLER_PATH') or define('DI_APP_SERVER_HANDLER_PATH', DI_APP_SERVER_PATH . '/Handler');
		//defined('DI_APP_SERVER_LISTENER_PATH') or define('DI_APP_SERVER_LISTENER_PATH', DI_APP_SERVER_PATH . '/Listeners');
		//defined('DI_APP_SERVER_REQUEST_PATH') or define('DI_APP_SERVER_REQUEST_PATH', DI_APP_SERVER_PATH . '/Request');
		//defined('DI_APP_SERVER_SERVICE_PATH') or define('DI_APP_SERVER_SERVICE_PATH', DI_APP_SERVER_PATH . '/Service');
		//defined('DI_APP_SERVER_TICKER_PATH') or define('DI_APP_SERVER_TICKER_PATH', DI_APP_SERVER_PATH . '/Ticker');
		//defined('DI_APP_SERVER_TEMP_PATH') or define('DI_APP_SERVER_TEMP_PATH', DI_APP_SERVER_PATH . '/Runtimes/Temp');
		//
		////Worker级
		//defined('DI_APP_SERVER_WORKER_PATH') or define('DI_APP_SERVER_WORKER_PATH', DI_APP_SERVER_PATH . '/Worker');
		//defined('DI_APP_SERVER_WORKER_COMMON_PATH') or define('DI_APP_SERVER_WORKER_COMMON_PATH', DI_APP_SERVER_WORKER_PATH . '/Common');
		//defined('DI_APP_SERVER_WORKER_CONFIG_PATH') or define('DI_APP_SERVER_WORKER_CONFIG_PATH', DI_APP_SERVER_WORKER_PATH . '/Conf');
		//
		////Server配置
		//defined('DI_LOG_PATH') or define('DI_LOG_PATH', DI_APP_SERVER_PATH . '/Log');
		//defined('DI_LOG_FILE_NAME') or define('DI_LOG_FILE_NAME', DI_SERVER_NAME . '.log');
	}

}
