<?php
/**
 * index db 扩展规则
 * @author wangtao
 *
 */

class Idb extends DbFactory {
	var $dbIdx; // 分库序号
	var $tableIdx; // 分表序号
	var $tableName;
	var $where;
	var $data;
	
	var $split_value;
	
	var $db_split_call_fun;
	var $table_split_call_fun = array ();
	
	
	static function getDb($key, $split_value = null) {
		if (empty ( $key )) {
			throw new Exception ( 'db key is not null' );
		}
		$dbConfig = dbConfig::getDbConfig ( $key, $split_value );
		$db = new Idb ( $dbConfig, $split_value );
		
		$db = $db->_getDb ();
		return $db;
		// self::$db [$key] = $db;
	}
	
	function __construct($dbConfig, $split_value = null) {
		// $dbConfig = dbConfig::getDbConfig($key);
		$this->dbConfig = $dbConfig;
		$this->split_value = $split_value;
		
		$this->db = parent::init_db_node ( $dbConfig );
		$this->db->setSplitValue($split_value) ;
		$this->db->database = $dbConfig['database'];
		// $this->db->splitCallBackFun = $this->splitCallBackFun;
		$this->db->setSqlCallFun ( array (
				$this,
				'nodeQuery' 
		) );
	}
	//only table split not for databases;
// 	public function setSplitValue($splitValue)
// 	{
// 		$this->split_value = $splitValue;
// 	}
	
	
	function nodeQuery($sql) {
		// $dbName = $this->dbConfig ['database'];
		$tableIdx = $this->getTableIdx ( $sql );
		// if( $this->db)
		// if ($this->dbIdx) {
		// $dbName = $this->dbConfig ['database'] . LINK_TAG . $this->db->dbIdx;
		// } else {
		// $dbName = $this->dbConfig ['database'];
		// }
		$dbName = $this->db->database;
		
		if ($tableIdx) {
			$tableIdx = Split::LINK_TAG . $tableIdx;
		} else {
			$tableIdx = '';
		}
		
		$pattern = '/(?![\'\"][\w\s]*)(update|into|from)\s+([\w]+)\s*(?![\w\s]*[\'\"])/usi';
		$replacement = "\$1 {$dbName}.\${2}{$tableIdx}  ";
		$sql = preg_replace ( $pattern, $replacement, $sql );
		
		// 返回默认值
		
		return $sql;
	}
	
	
	private function getTableIdx($sql) {
		$tableCallFun = $this->getTableCallFun ( $sql );
		if (empty ( $tableCallFun )) {
			return false;
		}
		
		if (empty (  $this->db->split_value )) {
			throw new Exception ( 'Table_split_value is null please set check init method !' );
		}
		$tableIdx = call_user_func ( $tableCallFun, $this->db->split_value );
		
		return $tableIdx;
	}
	
	private function getTableCallFun($sql) {
		if (array_key_exists ( 'data_split', $this->dbConfig )) {
			$callFunInfo = $this->dbConfig ['data_split'];
		} else {
			return false;
		}
		
		if (array_key_exists ( 'table_split_call_fun', $callFunInfo )) {
			$table_split_call_fun_arr = $callFunInfo ['table_split_call_fun'];
		} else {
			return false;
		}
		$tableName = $this->getTableName ( $sql );
		if ($tableName && array_key_exists ( $tableName, $table_split_call_fun_arr )) {
			$tableCallFun = $table_split_call_fun_arr [$tableName];
		} elseif (array_key_exists ( 'default', $table_split_call_fun_arr )) {
			$tableCallFun = $table_split_call_fun_arr ['default'];
		} else {
			$tableCallFun = '';
		}
		$this->table_split_call_fun = $tableCallFun;
		return $tableCallFun;
	
	}
	
	private function getTableName($sql) {
		$pattern = '/(?![\'\"][\w\s]*)(?:update|into|from)\s+([\w]+)\s*(?![\w\s]*[\'\"])/usi';
		// $replacement = "\$1 {$dbName}.\${2}{$tableIdx} ";
		
		if (preg_match ( $pattern, $sql, $matches )) {
			return $matches [1];
		} else {
			return false;
		}
	}
	
	// static function setTableIdx($splitValue) {
	
	
	// }
	// private function setDbName() {
	
	// }
	
	// private function setTableIdx() {
	
	// if (empty ( $this->split_key_value ))
	// return false;
	// else {
	// $mdKey = md5 ( $this->split_key_value );
	// return $mdKey [0];
	// }
	// }
	
	// private function getMaxTableIdx($maxUserId) {
	
	// if (($count % self::MAX_USER_PRE_DB) == 0) {
	// $maxIdx = ceil ( self::MAX_USER_PRE_DB / self::MAX_USER_PRE_TABLE );
	// } else {
	// $maxIdx = ceil ( ($count % self::MAX_USER_PRE_DB) /
	// self::MAX_USER_PRE_TABLE );
	// }
	// return $maxIdx;
	// }
	// private function getMaxDbIdx($maxUserId) {
	// return $count == 0 ? 1 : ceil ( $maxUserId / self::MAX_USER_PRE_DB );
	// }

}