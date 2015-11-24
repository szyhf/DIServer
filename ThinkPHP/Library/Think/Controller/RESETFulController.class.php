<?php

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */
    namespace Think\Controller;
use Think\Controller;
    /**
     * Description of RESETFulController
     *
     * @author Back
     */
    class ResetfulController extends Controller
    {

	/**
	 * 目前只支持json
	 * @param array $data
	 * @param type $type 预留参数，暂时没用
	 * @param int $status 状态码，默认200 ok
	 * @return string 
	 * 
	 */
	protected function response ($data, $type = 'json', $status = 200)
	{
	    if (empty($data))
		$data = '';
	   	    
	    
	    $baseData = $this->baseResponseDataCreate($status);
	    $baseData['More']=$data;
	    
	     $baseData = json_encode($baseData);
	    $this->setContentType($type);
	    exit($baseData);
	}

	/**
	 * 设置页面输出的CONTENT_TYPE和编码
	 * @access public
	 * @param string $type content_type 类型对应的扩展名
	 * @param string $charset 页面输出编码
	 * @return void
	 */
	private function setContentType ($type, $charset = '')
	{
	    if (headers_sent())
		return;
	    if (empty($charset))
		$charset = C('DEFAULT_CHARSET');
	    $type = strtolower($type);
	    if (isset($this->allowOutputType[$type])) //过滤content_type
		header('Content-Type:application/json; charset=' . $charset);
	}

	private function httpStatus ($code)
	{
	    static $_status = array (
		    // Informational 1xx
		    100	 => 'Continue',
		    101	 => 'Switching Protocols',
		    // Success 2xx
		    200	 => 'OK',
		    201	 => 'Created',
		    202	 => 'Accepted',
		    203	 => 'Non-Authoritative Information',
		    204	 => 'No Content',
		    205	 => 'Reset Content',
		    206	 => 'Partial Content',
		    // Redirection 3xx
		    300	 => 'Multiple Choices',
		    301	 => 'Moved Permanently',
		    302	 => 'Moved Temporarily ', // 1.1
		    303	 => 'See Other',
		    304	 => 'Not Modified',
		    305	 => 'Use Proxy',
		    // 306 is deprecated but reserved
		    307	 => 'Temporary Redirect',
		    // Client Error 4xx
		    400	 => 'Bad Request',
		    401	 => 'Unauthorized',
		    402	 => 'Payment Required',
		    403	 => 'Forbidden',
		    404	 => 'Not Found',
		    405	 => 'Method Not Allowed',
		    406	 => 'Not Acceptable',
		    407	 => 'Proxy Authentication Required',
		    408	 => 'Request Timeout',
		    409	 => 'Conflict',
		    410	 => 'Gone',
		    411	 => 'Length Required',
		    412	 => 'Precondition Failed',
		    413	 => 'Request Entity Too Large',
		    414	 => 'Request-URI Too Long',
		    415	 => 'Unsupported Media Type',
		    416	 => 'Requested Range Not Satisfiable',
		    417	 => 'Expectation Failed',
		    // Server Error 5xx
		    500	 => 'Internal Server Error',
		    501	 => 'Not Implemented',
		    502	 => 'Bad Gateway',
		    503	 => 'Service Unavailable',
		    504	 => 'Gateway Timeout',
		    505	 => 'HTTP Version Not Supported',
		    509	 => 'Bandwidth Limit Exceeded'
	    );	    
	    return $_status;
	}
	
	private function baseResponseDataCreate($code)
	{
	    $msg = '';
	    if(isset($this->httpStatus[$code]))
		$msg = $this->httpStatus[$code];
	    return [
		    'Status'=>$code,
		    'Message'=>$msg,
		    'More'=>''
	    ];
	}
    }
    