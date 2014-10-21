<?php
/**
 * udb::create($uuid);
 * Enter description here ...
 * @author Administrator
 *
 */
class Ucache 
{
	var $cache;
	var $caceMain;
	static function create($uuId) 
	{
		return new Ucache ( $uuId );
	}
	function Ucache( $uuId=0 ) 
	{
		global $syncMcConfig,$storeMcConfig ;
// 		parent::__construct ('user', $uuId );
        $db = stra::uDb('user',$uuId);
		$config = stra::ac('config');
		$this->cache = new cacheData ( $config ['user_cache_path'] . '/' . cache_by_date () . String::hash ( $uuId, HASH_LEVEL ) . '/' . $uuId, $db, $GLOBALS ['syncMcConfig'], $GLOBALS ['storeMcConfig'] , false );
	}
}
?>