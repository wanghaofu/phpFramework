<?php
PHPINFO();
//  function determineBrowser ($Agent) {
//         $browseragent="";   //浏览器
//         $browserversion=""; //浏览器的版本
//         if (preg_match('/MSIE ([0-9].[0-9]{1,2})/',$Agent,$version)) {
//             $browserversion=$version[1];
//             $browseragent="Internet Explorer";
//         } else if (preg_match( '/Opera\([0-9]{1,2}.[0-9]{1,2})/',$Agent,$version)) {
//             $browserversion=$version[1];
//             $browseragent="Opera";
//         } else if (preg_match( '/Firefox\/([0-9.]{1,5})/',$Agent,$version)) {
//             $browserversion=$version[1];
//             $browseragent="Firefox";
//         }else if (preg_match( '/Chrome\/([0-9.]{1,3})/',$Agent,$version)) {
//             $browserversion=$version[1];
//             $browseragent="Chrome";
//         }else if (preg_match( '/Safari\/([0-9.]{1,3})/',$Agent,$version)) {
//             $browseragent="Safari";
//             $browserversion="";
//         }else {
//             $browserversion="";
//             $browseragent="Unknown";
//         }
//         return $browseragent." ".$browserversion;
//     }
//      同理获取访问用户的操作系统的信息
//     function determinePlatForm ($Agent) {
//         $browserplatform=='';
//         if (eregi('win',$Agent) && strpos($Agent, '95')) {
//             $browserplatform="Windows 95";
//         }else if (eregi('win 9x',$Agent) && strpos($Agent, '4.90')) {
//             $browserplatform="Windows ME";
//         }else if (eregi('win',$Agent) && ereg('98',$Agent)) {
//             $browserplatform="Windows 98";
//         }else if (eregi('win',$Agent) && eregi('nt 5.0',$Agent)) {
//             $browserplatform="Windows 2000";
//         }else if (eregi('win',$Agent) && eregi('nt 5.1',$Agent)) {
//             $browserplatform="Windows XP";
//         }else if (eregi('win',$Agent) && eregi('nt 6.0',$Agent)) {
//             $browserplatform="Windows Vista";
//         }else if (eregi('win',$Agent) && eregi('nt 6.1',$Agent)) {
//             $browserplatform="Windows 7";
//         }else if (eregi('win',$Agent) && ereg('32',$Agent)) {
//             $browserplatform="Windows 32";
//         }else if (eregi('win',$Agent) && eregi('nt',$Agent)) {
//             $browserplatform="Windows NT";
//         }else if (eregi('Mac OS',$Agent)) {
//             $browserplatform="Mac OS";
//         }else if (eregi('linux',$Agent)) {
//             $browserplatform="Linux";
//         }else if (eregi('unix',$Agent)) {
//             $browserplatform="Unix";
//         }else if (eregi('sun',$Agent) && eregi('os',$Agent)) {
//             $browserplatform="SunOS";
//         }else if (eregi('ibm',$Agent) && eregi('os',$Agent)) {
//             $browserplatform="IBM OS/2";
//         }else if (eregi('Mac',$Agent) && eregi('PC',$Agent)) {
//             $browserplatform="Macintosh";
//         }else if (eregi('PowerPC',$Agent)) {
//             $browserplatform="PowerPC";
//         }elseif (eregi('AIX',$Agent)) {
//             $browserplatform="AIX";
//         }else if (eregi('HPUX',$Agent)) {
//             $browserplatform="HPUX";
//         }else if (eregi('NetBSD',$Agent)) {
//             $browserplatform="NetBSD";
//         }else if (eregi('BSD',$Agent)) {
//             $browserplatform="BSD";
//         }elseif (ereg('OSF1',$Agent)) {
//             $browserplatform="OSF1";
//         }else if (ereg('IRIX',$Agent)) {
//             $browserplatform="IRIX";
//         }else if (eregi('FreeBSD',$Agent)) {
//             $browserplatform="FreeBSD";
//         }if ($browserplatform=='') {$browserplatform = "Unknown"; }
//         return $browserplatform;
//     } 

    
    /**
     *   类名:   mobile
     *   描述:   手机信息类
     *   其他:   偶然   编写
     */
    
    class   mobile{
    	/**
    	 *   函数名称:   getPhoneNumber
    	 *   函数功能:   取手机号
    	 *   输入参数:   none
    	 *   函数返回值:   成功返回号码，失败返回false
    	 *   其它说明:   说明
    	 */
    	function   getPhoneNumber(){
    		if   (isset($_SERVER[ 'HTTP_X_NETWORK_INFO '])){
    			$str1   =   $_SERVER[ 'HTTP_X_NETWORK_INFO '];
    			$getstr1   =   preg_replace( '/(.*,)(11[d])(,.*)/i ', '\2 ',$str1);
    			Return   $getstr1;
    		}elseif   (isset($_SERVER[ 'HTTP_X_UP_CALLING_LINE_ID '])){
    			$getstr2   =   $_SERVER[ 'HTTP_X_UP_CALLING_LINE_ID '];
    			Return   $getstr2;
    		}elseif   (isset($_SERVER[ 'HTTP_X_UP_SUBNO '])){
    			$str3   =   $_SERVER[ 'HTTP_X_UP_SUBNO '];
    			$getstr3   =   preg_replace( '/(.*)(11[d])(.*)/i ', '\2 ',$str3);
    			Return   $getstr3;
    		}elseif   (isset($_SERVER[ 'DEVICEID '])){
    			Return   $_SERVER[ 'DEVICEID '];
    		}else{
    			Return   false;
    		}
    	}
    
    	/**
    	 *   函数名称:   getHttpHeader
    	 *   函数功能:   取头信息
    	 *   输入参数:   none
    	 *   函数返回值:   成功返回号码，失败返回false
    	 *   其它说明:   说明
    	 */
    	function   getHttpHeader(){
    		$str   =   ' ';
    		foreach   ($_SERVER   as   $key=> $val){
    			$gstr   =   str_replace( "& ", "& ",$val);
    			$str.=   "$key   ->   ".$gstr. "\r\n ";
    		}
    		Return   $str;
    	}
    
    	/**
    	 *   函数名称:   getUA
    	 *   函数功能:   取UA
    	 *   输入参数:   none
    	 *   函数返回值:   成功返回号码，失败返回false
    	 *   其它说明:   说明
    	 */
    	function   getUA(){
    		if   (isset($_SERVER[ 'HTTP_USER_AGENT '])){
    			Return   $_SERVER[ 'HTTP_USER_AGENT '];
    		}else{
    			Return   false;
    		}
    	}
    
    	/**
    	 *   函数名称:   getPhoneType
    	 *   函数功能:   取得手机类型
    	 *   输入参数:   none
    	 *   函数返回值:   成功返回string，失败返回false
    	 *   其它说明:   说明
    	 */
    	function   getPhoneType(){
    		$ua   =   $this-> getUA();
    		if($ua!=false){
    			$str   =   explode( '   ',$ua);
    			Return   $str[0];
    		}else{
    			Return   false;
    		}
    	}
    
    	/**
    	 *   函数名称:   isOpera
    	 *   函数功能:   判断是否是opera
    	 *   输入参数:   none
    	 *   函数返回值:   成功返回string，失败返回false
    	 *   其它说明:   说明
    	 */
    	function   isOpera(){
    		$uainfo   =   $this-> getUA();
    		if   (preg_match( '/.*Opera.*/i ',$uainfo)){
    			Return   true;
    		}else{
    			Return   false;
    		}
    	}
    
    	/**
    	 *   函数名称:   isM3gate
    	 *   函数功能:   判断是否是m3gate
    	 *   输入参数:   none
    	 *   函数返回值:   成功返回string，失败返回false
    	 *   其它说明:   说明
    	 */
    	function   isM3gate(){
    		$uainfo   =   $this-> getUA();
    		if   (preg_match( '/M3Gate/i ',$uainfo)){
    			Return   true;
    		}else{
    			Return   false;
    		}
    	}
    
    	/**
    	 *   函数名称:   getHttpAccept
    	 *   函数功能:   取得HA
    	 *   输入参数:   none
    	 *   函数返回值:   成功返回string，失败返回false
    	 *   其它说明:   说明
    	 */
    	function   getHttpAccept(){
    		if   (isset($_SERVER[ 'HTTP_ACCEPT '])){
    			Return   $_SERVER[ 'HTTP_ACCEPT '];
    		}else{
    			Return   false;
    		}
    	}
    
    	/**
    	 *   函数名称:   getIP
    	 *   函数功能:   取得手机IP
    	 *   输入参数:   none
    	 *   函数返回值:   成功返回string
    	 *   其它说明:   说明
    	 */
    	function   getIP(){
    		$ip=getenv( 'REMOTE_ADDR ');
    		$ip_   =   getenv( 'HTTP_X_FORWARDED_FOR ');
    		if   (($ip_   !=   " ")   &&   ($ip_   !=   "unknown ")){
    			$ip=$ip_;
    		}
    		return   $ip;
    	}
    }
    
    $mo = new mobile();
 
 $Agent = $_SERVER['HTTP_USER_AGENT'];

 echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">';
 echo '<body>';
 echo '<pre>';
 echo '<b>Head Info: </b>'. $Agent;
 echo $x =$mo-> getHttpAccept();
 echo $x.=$mo->getHttpHeader();
 
//    echo '<br><b>Browseragent Info:</b> '.  determineBrowser ($Agent);
//    echo '<b>Sys Info :</b> '.determinePlatForm ($Agent);
   echo '</pre>';
   echo '</body>';
   echo '</html>';
   error_log($x,3,'./brower.log');
    ?>