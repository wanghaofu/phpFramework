<?php
#exception
require_once ('lib/sys/Sys.php');

class stra extends sys {
	public static function data($moduleName) {
	
	}
	//out alis
	public static function ac($moduleName) {
		
	}
	//out alis
	public static function assign($key, $value) {
	
	}
	public static function Exception($errorId) 
	{
	
	}
	public static function log($info, $key = '') {
		static $time;
		static $num;
		$path = '/www/stra/logs/snk_run_error.log';
		if (! $time) {
			$time = date ( 'Y-m-d H:i:s' );
			error_log ( "\n$time ###\n", 3, $path );
		} else {
			error_log ( "\n", 3, '$path' );
		}
		error_log ( $info, 3, $path );
	}
}
