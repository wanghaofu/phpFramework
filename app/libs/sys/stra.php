<?php
#exception
require_once ('./libs/sys/Sys.php');
class stra extends sys {
	static $db = array ();
	static $udb =array();
	static $cache;
	static $ucache = array();
	static $uuid;
	static $lang;
	static $IN;
	//	static $ouuid;
	
	static public function initIn($in)
	{
		self::$IN= $in;
	}

	static public function setgUuid($uuid) {
		self::$uuid = $uuid;
	}
	static public function initcache($gcache) 
	{
		self::$cache = $gcache;
	}
	
	//out alis
	public static function assign($key, $value) {
	
	}
	public static function Exception($errorId) {
	
	}
	//获取db
	public static function db($key = 'stra', $split_value = null ) {
		$ckey = $key.$split_value;
		if (empty ( self::$db[$ckey] )) {
			self::$db[$ckey] = Idb::getDb($key,$split_value);
		}
		return self::$db [$ckey];
	}
	/**
	 * return udb objcet  default return self udb else is other udb objcet
	 * Enter description here ...
	 * @param unknown_type $uuid
	 */
	public static function uDb($uuid = 0) {
		if( empty( $uuid) )
		{
			throw new Exception('$uuid is can not null!');
		}
		$uuid = intval( $uuid );
		if (empty ( $uuid )) {
			return self::_gUdb();
		} else {
			return self::_uudb( $uuid );
		}
	}
	
	private static function _gUdb() {
		if (empty ( self::$uuid )) {
			throw new Exception ( 'uuid is not empty' );
		}
		if (empty ( self::$udb ['udb_' . self::$uuid] )) {
// 			$udb = new Idb ( self::$uuid );
			$udb = self::db ('user',  self::$uuid );
			return self::$udb ['udb_' . self::$uuid] = $udb ;
		
		} else {
			return self::$udb ['udb_' . self::$uuid];
		}
	}
	
	private static function _uudb($uuid) {
		if (empty ( self::$udb ["udb_$uuid"] )) {
			$udb = self::db ('user', $uuid );
			self::$udb ["udb_$uuid"] = $udb ;
			return self::$udb ["udb_$uuid"];
		} else {
			return self::$udb ["udb_$uuid"];
		}
	}
	
	public static function uCache($uuid = 0) {
		if (empty ( $uuid )) {
			return self::_gUcache ();
		} else {
			return self::_uuCache ( $uuid );
		}
	}
	
	private static function _gUcache() {
		if (empty ( self::$uuid )) {
			throw new Exception ( 'uuid is not empty' );
		}
		if (empty ( self::$ucache ['ucache_' . self::$uuid] )) {
			$uCache = new Ucache ( self::$uuid );
			self::$ucache ['ucache_' . self::$uuid] = $uCache->cache;
			return self::$ucache ['ucache_' . self::$uuid];
		} else {
			return self::$ucache ['ucache_' . self::$uuid];
		}
	
	}
	private static function _uuCache($uuid) {
		if (empty ( self::$ucache ["ucache_$uuid"] )) {
			$uCache = new Ucache ( $uuid );
			self::$ucache ["ucache_$uuid"] = $uCache->cache;
			return self::$ucache ["ucache_$uuid"];
		} else {
			return self::$ucache ["ucache_$uuid"];
		}
	}
	
	//out alis
	public static function ac($key) {
		return self::$cache->getData ( $key );
	}
	public static function get($key) {
		return self::$cache->getData ( $key );
	}
	
	public static function set($key, $value) {
		self::$cache->addData ( $key, $value );
	}
	
	//	public static function initUac($uuid)
	//	{
	//		self::$ouuid = $uuid;
	//	}
	

	public static function uac($key, $uuid = 0) {
		if (empty ( $uuid )) {
			$ucache = self::uCache ();
		} else {
			$ucache = self::uCache ( $uuid );
		}
		return $ucache->getData ( $key );
	}
	
	public static function log($info, $key = '') {
		static $time;
		static $num;
		$path = '/www/stra/logs/error.log';
		if (! $time) {
			$time = date ( 'Y-m-d H:i:s' );
			error_log ( "\n$time ###\n", 3, $path );
		} else {
			error_log ( "\n", 3, '$path' );
		}
		error_log ( $info, 3, $path );
	}
	static function initLang() {
		// 初始化页面输出类
		if (empty ( self::$lang )) 
		{
			$config = stra::ac ( 'config' );
			$lang = new Language ( $config ['default_language'] );
			$lang->init ();
		}else {
			return self::$lang;
		}
	}
	
	static function createCache($dbKey)
	{
		$db = self::db($dbKey);
		$cache = new cacheData ( CACHE_PATH,  $db,  $GLOBALS['syncMcConfig'], $GLOBALS['storeMcConfig'] );
		return $cache;
	}
}
