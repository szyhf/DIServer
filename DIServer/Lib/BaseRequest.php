<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer;
/**
 * 给客户端的请求基类
 * @author Back
 */
abstract class BaseRequest
{
    protected $ID = -1;
    protected $Params = '';

    public function __construct()
    {
	;
    }

    public function ToPackage()
    {
	return Package::CreatePackage($this->ID, $this->Params);
    }
}
