<?php
class Split {
	const USER_DB_SIZE = 20000; //not zero
	const USER_TABLE_SPLIT_NUM = 1;
	
	const LINK_TAG = '_';
	
	const USER_INDEX_SPLIT_NUM = 100000;
	
	static function default_rule($split_value) {
		return null;
	}
	static function uuid($split_value) {
		return $split_value % max( 1, self:: USER_INDEX_SPLIT_NUM );
	}
	static function puuid($split_value) 
	{
		$mdKey = md5 ( $split_value );
		return $mdKey[0];
	}
	
	// example
	static function setDbIdx($splitValue) {
		return null;
	}
	static function userTable( $split_value )
	{
		return max(1,$split_value % max(1, self::USER_TABLE_SPLIT_NUM ) );
	}
	static function userDb( $split_value )
	{
		$dbIndex = intval(($split_value - 1) / self::USER_DB_SIZE );
		$dbIndex = max( 0 , $dbIndex );
		return $dbIndex;
	}
	
	// $offset : 偏移量, 用于定位数据库
	// $size : 每个数据库的最大偏移量
// 	function init_user_db($offset, $size, $autoCommit = false)
}