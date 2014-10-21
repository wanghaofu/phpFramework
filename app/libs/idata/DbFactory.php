<?php
class DbFactory
{
	static $dbNodeConns = array();
	var $db;
	var $split_value;
	static $dbConfig = array();
	protected  function _getDb($dbkey = '')
	{
		return $this->db;
	}
	
	public function setSplitValue($split_value)
	{
		$this->split_value = $split_value;
	}
	
	// 初始化db节点 按照数据库实例 一台服务器  或者同台不同端口的数据库服务
	static function init_db_node($dbConfig, $autoCommit = true, $persistent = false)
	{
		
		$db = self::init_db($dbConfig);
		$db->setNode(true);
		$db->setDatabase($dbConfig['database']);
		
		return $db;
	}
	
	
	
	
	
	/** 初始化全新的db为每一个数据库操作建立db **/
	static function db($key)
	{
		$dbConfig = dbConfig::getDbConfig($key);
		$db = self::init_db($dbConfig);
		return $db;
	}
	static function init_db($dbConfig, $autoCommit = true, $persistent = false)
	{
		$db = new db($dbConfig['dsn'], $dbConfig['user'], $dbConfig['password'], $dbConfig['database'], $autoCommit, $dbConfig['charset'], $persistent);
		if (isset($GLOBALS['gMirrorId']) && $GLOBALS['gMirrorId'] > 0)
		{
			$db->readOnly = true;
		}
		if (!$autoCommit)
			$db->begin();
		
		return $db;
	}
	/**
	 * no 
	 * Enter description here ...
	 * @param unknown_type $sql
	 */
	function baseNodeQuery($sql)
	{
		$dbName = $this->dbConfig['database'];
		
		$pattern = '/(?![\'\"][\w\s]*)(update|into|from)\s+([\w]+)\s*(?![\w\s]*[\'\"])/usi';
		$replacement = "\$1 {$dbName}.\${2}" ;
		$sql = preg_replace($pattern, $replacement, $sql);
		return $sql;
	}
	
}

?>