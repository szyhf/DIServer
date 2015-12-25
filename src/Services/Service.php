<?php

namespace DIServer\Services;

use \DIServer\Application as Application;

/**
 * Description of Service
 *
 * @author Back
 */
abstract class Service
{
    /**
     * 当前主程
     * @var \DIServer\Application
     */
    private $app;

    public function __construct(Application $app)
    {
	$this->SetApp($app);
    }
    
    /**
     * 注册当前服务
     */
    public function Register()
    {
	
    }

    /**
     * 获取当前主程
     * @return \DIServer\Application
     */
    protected function App()
    {
	return $this->app;
    }

    /**
     * 
     * @param \DIServer\Application $app
     */
    protected function SetApp(Application $app)
    {
	$this->app = $app;
    }

}
