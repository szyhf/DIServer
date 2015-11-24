<?php

namespace DIServer;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 用来打包和解包
 *
 * @author Back
 */
class Package
{

    /**
     * 生成数据包
     * @param type $handlerID 收包者约定唯一ID
     * @param type $params 包正文，如果是string会自动转换；如果不是string则会直接发送。
     * @return byte[] 以btye形式打好的消息数据包
     */
    public static function CreatePackage($handlerID, $params = "")
    {
	$packedHandlerID = pack('L', $handlerID);
	$packedLength = pack('L', strlen($params) + 8);
	$msg = $packedLength . $packedHandlerID . $params;
	return $msg;
    }

    public static function GetHandlerID($byteData)
    {
	$handlerID = NULL;
	if (strlen($byteData)>=8)
	{
	    $handlerID = unpack('x4/i1HandlerID', $byteData);
	    if ($handlerID)
	    {
		$handlerID = array_pop($handlerID);
	    }
	}
	return $handlerID;
    }

    public static function GetParams(&$byteData)
    {
	return end(unpack('x8/a*', $byteData));
    }

}
