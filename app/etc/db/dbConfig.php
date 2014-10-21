<?php
class dbConfig {
	/**
	 * * 主数据库配置 **
	 */
	static $dbConfigs = array ();
	
	static $dbConfig = array ();
	
	var $key;
	
	var $cacheKey;
	
	var $split_value;
	
	var $dbIdx;
	
	// split config
	
	static function setDbConfigs($dbConfig) 
	{
		self::$dbConfigs = $dbConfig;
	}
	
	static function getDbConfig($key, $split_value = 'null') {
		$config = new dbConfig ( $key, $split_value );
		$dbConfig = $config->_getDbConfig ( $key, $split_value );
		return $dbConfig;
	}
	
	function __construct($key, $split_value = 'null') {
		// $this->dbConfig = include('./etc/db.inc.php');
		$this->key = $key;
		$this->split_value = $split_value;
		$this->cacheKey = $this->getCacheKey ();
	}
	
	private function _getDbConfig($key, $split_value = 'null') {
		$dbConfig = array ();
		// $this->dbConfig [$this->cacheKey] = self::$dbConfig [$key];
		$this->dbConfig = self::$dbConfigs [$key];
		
		$dbConfig = $this->dbConfig;
		$dsn = $this->getDsn ();
		$database = $this->getDatabase ();
		
		$dbConfig ['dsn'] = $dsn;
		$dbConfig ['database'] = $database;
		return $dbConfig;
	}
	
	private function getDatabase() {
		$dbIdx = $this->getDbIdx ();
		if( is_null($dbIdx)  )
		{
			$database =  $this->dbConfig ['database'];
		}else{
			$database = $this->dbConfig ['database'] .Split::LINK_TAG. $dbIdx;
		}
		return  $database;
	}
	private function getDbIdx() {
		
		$db_split_call_fun = $this->getDbCallBackFun ();
		
		if (empty ( $db_split_call_fun )) {
			return null;
		}
		
		if (empty ( $this->split_value )) {
			throw new Exception ( "split_value is not config for $db_split_call_fun!" );
		}
		
		return call_user_func ( $db_split_call_fun, $this->split_value );
	
	}
	
	private function getDbCallBackFun() {
		if( isset($this->dbConfig['data_split']) && is_array($this->dbConfig['data_split']))
		{
			$data_split = $this->dbConfig['data_split'];
		}else{
			return false;
		}
		
		if( array_key_exists('db_split_call_fun', $data_split))
		{
		return $data_split ['db_split_call_fun'];
		}else{
			return false;
		}
	}
	
	
	
	private function getDsn() {
		$dsnInfo = $this->dbConfig ['dsn'];
		
		if ( is_array ( $dsnInfo ) ) 
		{
			$Idx = $this->getDbIdx ();
			$dsnIdx = $this->getDsnIdx ( $Idx );
			return $dsnInfo [$dsnIdx];
		} elseif ($dsnInfo) 
		{
			return $dsnInfo;
		} else 
		{
			throw new Exception ( 'dsn is not config!' );
		}
	}
	
	private function setDbSplit() 
	{
	}
	
	private function getDsnIdx($Idx) {
		$dbIdxStr = $this->dbConfig ['dbIdx'];
		$dbIdxArr = explode ( ',', $dbIdxStr );
		foreach ( $dbIdxArr as $key => $value ) {
			$dbIdxInfo = explode ( ':', $value );
			if ($dbIdxInfo [0] == $Idx) {
				$DnsIdx = $dbIdxInfo [0];
				return $DnsIdx;
				break;
			}
		}
	}
	
	private function getCacheKey() {
		return $this->key . $this->split_value;
	}
	
	private function _setDbConfig($split_value) {
	
	}
	
	public function getTableIdx() {
		return $this->tableIdx;
	}
	private function setDbIdx() {
	
	}
	
	private function setTableIdx() {
	
	}
	
	function setSplitValue($value) {
		$this->split_value = $value;
	}
	
	// function setIdx() {
	// if (! is_null ( $this->dbIdxCallFun ) && $this->split_value) // only give
	// // on
	// {
	// $this->dbIdx = call_user_func ( $this->dbIdxCallFun, $this->split_value
	// );
	// }
	
	// if (! is_null ( $this->tableIdxCallFun ) && $this->split_value) {
	// $this->tableIdx = call_user_func ( $this->tableIdxCallFun,
	// $this->split_value );
	// }
	// }
	
	// public function setDbIdxCallFun($callFun) {
	// $this->dbIdxCallFun = $callFun;
	// }
	// public function setTableIdxCallFun($callFun) {
	// $this->tableIdxCallFun = $callFun;
	// }
	
	// function getConfig($dbkey)
	// {
	
	// $this->_getConfig(self::$dbConfig[$dbKey]);
	// }
	
	// private function _getConfig($dbConfig)
	// {
	
	// return $dbConfig;
	
	// }
	
	// /**
	// * * 用户扩展数据库配置 **
	// */
	// static public function getExtConfig($idx) {
	// $dbServersDefault = self::$dbExtConfig ['default'];
	// $dbServers = self::$dbExtConfig ['dbServers'];
	
	// foreach ( $dbServers as $key => $dbserver ) {
	// if (in_array ( $idx, $dbserver ['idx'] )) {
	// $dbServer = $dbserver;
	// break;
	// }
	// }
	// if (empty ( $dbServer )) {
	// throw new Exception ( 'use db $dbExtConfig[' . $idx . '].is not config
	// please check you db.inc.class php file!' );
	// }
	
	// $dbExtConfig = $dbServer; // 数据库连接字符串
	// if (empty ( $dbExtConfig ['database'] )) {
	// $dbExtConfig ['database'] = "{$dbServersDefault['database']}_$idx"; //
	// 数据库
	// }
	// if (empty ( $dbExtConfig ['user'] )) {
	// $dbExtConfig ['user'] = $dbServersDefault ['user']; // 登陆用户
	// }
	// if (empty ( $dbExtConfig ['password'] )) {
	// $dbExtConfig ['password'] = $dbServersDefault ['password']; // 登陆密码
	// }
	// if (empty ( $dbExtConfig ['charset'] )) {
	// $dbExtConfig ['charset'] = $dbServersDefault ['charset']; // 编码
	// }
	// // unset($dbExtConfig['idx']);
	// return $dbExtConfig;
	// }

}
?>