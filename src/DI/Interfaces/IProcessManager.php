<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace DIServer\DI\Interfaces;

/**
 * Server进程管理类
 * 
 * @author Back
 */
interface IProcessManager
{
    public function GetMasterPID();
}
