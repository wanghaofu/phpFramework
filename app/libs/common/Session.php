<?php

/******************************************************************

	Name: Session ( Cookie ) 处理函数库

 ******************************************************************/

class Session {
	// 读取 Session
	public static function get($varName, $useCookie = 0, $encryptCode = null) {
		$encryptCode = ! is_null ( $encryptCode ) ? $encryptCode : SYSTEM_CODE;
		
		if ($useCookie) {
			$arrayReturn = $_COOKIE [$varName];
			if ($arrayReturn == 'deleted')
				return;
		} else {
			@session_start ();
			$arrayReturn = $_SESSION [$varName];
		}
		return _decrypt ( $arrayReturn, $encryptCode );
	}
	
	// 保存 Session
	public static function set($varName, $value, $useCookie = 0, $expire = null, $domain = null, $encryptCode = null) {
		$encryptCode = ! is_null ( $encryptCode ) ? $encryptCode : SYSTEM_CODE;
		$value = _encrypt ( $value, $encryptCode );
		
		if ($useCookie) {
			$domain = ! is_null ( $domain ) ? $domain : (defined ( "COOKIE_DOMAIN" ) ? COOKIE_DOMAIN : '');
			if ($expire > 0) {
				$expire += time ();
			}
			setCookie ( $varName, $value, $expire, '/', $domain );
			$_COOKIE [$varName] = $value;
		} else {
			if (! session_is_registered ( $varName )) {
				session_register ( $varName );
			}
			$_SESSION [$varName] = $value;
		}
		return $value;
	}
	
	// 删除 Session
	public static function move($varName, $useCookie = 0, $domain = null) {
		if ($useCookie) {
			$domain = ! is_null ( $domain ) ? $domain : (defined ( "COOKIE_DOMAIN" ) ? COOKIE_DOMAIN : '');
			setCookie ( $varName, '', 0, '/', $domain );
		} else {
			session_unregister ( $varName );
		}
		return true;
	}
	
	// 读取所有 Session
	public static function getAll($useCookie = 0, $encryptCode = null) {
		if ($useCookie) {
			$sessions = $_COOKIE;
		} else {
			@session_start ();
			$sessions = $_SESSION;
		}
		
		$encryptCode = ! is_null ( $encryptCode ) ? $encryptCode : SYSTEM_CODE;
		$arrayReturn = array ();
		while ( list ( $key, $item ) = @each ( $sessions ) ) {
			$arrayReturn [$key] = _decrypt ( $item, $encryptCode );
		}
		return $arrayReturn;
	}
	
	// 删除所有 Session
	public static function flush($useCookie = 0) {
		$sessions = _load_all_sessions ( $useCookie );
		while ( list ( $key, $value ) = @each ( $sessions ) ) {
			_delete_session ( $key, $useCookie );
		}
		return true;
	}
	
	// 生成 Session 加密串
	public static function make_session_code($data, $encryptCode = null) {
		$encryptCode = ! is_null ( $encryptCode ) ? $encryptCode : SYSTEM_CODE;
		if (! is_array ( $data ))
			$data = array ();
		array_push ( $data, $encryptCode );
		$sessionCode = md5 ( @join ( '-', $data ) );
		return $sessionCode;
	}
}
?>