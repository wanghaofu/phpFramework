<?php
/******************************************************************
Name: 数据库初始化
Author: 王涛
Email: wanghaofu@163.com
QQ: 595900598
 ******************************************************************/

if (!defined('IN_SYSTEM'))
{
	exit('Access Denied');
}

function init_db($dbConfig, $autoCommit = false, $persistent = false)
{
	global $__gDatabases__;
	
	$db = new db($dbConfig['dsn'], $dbConfig['user'], $dbConfig['password'], $dbConfig['database'], $autoCommit, $dbConfig['charset'], $persistent);
	if (isset($GLOBALS['gMirrorId']))
	{
		if ($GLOBALS['gMirrorId'] > 0)
		{
			$db->readOnly = true;
		}
	}
	//if ( !$autoCommit ) $db->begin ();
	if (!is_array($__gDatabases__))
		$__gDatabases__ = array();
	
	$__gDatabases__[] = &$db;
	
	return $db;
}

// 批量提交查询
function db_batch_commit(&$dbArray)
{
	if (!is_array($dbArray))
		return false;
	reset($dbArray);
	while (list($key, $db) = @each($dbArray))
	{
		$db->commit();
		$db->begin();
	}
	reset($dbArray);
	return true;
}

// 批量关闭数据库连接
function db_batch_close(&$dbArray)
{
	if (!is_array($dbArray))
		return false;
	reset($dbArray);
	while (list($key, $db) = @each($dbArray))
	{
		@$dbArray[$key]->close();
		$dbArray[$key] = null;
		unset($dbArray[$key]);
	}
	return true;
}

// 关闭所有数据库连接
function db_close_all()
{
	global $__gDatabases__;
	db_batch_close($__gDatabases__);
}

// 更新并获取 last id
// $idField: 字段名, $increase: 增量, $table: 表名
function update_last_id($idField, $increase = 1, $table = 'last_ids')
{
	$GLOBALS['db']->begin();
	$GLOBALS['db']->query("UPDATE $table SET $idField = LAST_INSERT_ID($idField + $increase)");
	$lastId = $GLOBALS['db']->lastInsertId();
	$GLOBALS['db']->commit();
	return $lastId;
}

// 	获取当前的 last id
// $idField: 字段名
function get_last_id($idField, $table = 'last_ids')
{
	$result = $GLOBALS['db']->select($table, null, null, 1);
	$lastId = $result[0][$idField];
	return $lastId;
}

// 初始化数据库类  主库
function db_class_init()
{
	global $db, $dbSlave, $dbConfig;
	
	if ($db)
	{
		$db->close();
		$db = null;
	}
	
	if ($dbSlave)
	{
		$dbSlave->close();
		$dbSlave = null;
	}
	
	// 初始化主数据库 master 连接
	$mainDbconfig = $dbConfig['master'];
	$db = init_db($mainDbconfig);
	
//	$connKey = dbFactory::getConnKey($mainDbconfig['dsn']);
//	dbFactory::$dbNodeConns[$connKey] = &$db;
	$slaverDbConfig = $dbConfig['slave'];
	
	// 初始化主数据库 slave 连接
	if (is_array($dbConfig['slave']))
	{
		$dbSlave = init_db($slaverDbConfig);
//		$slaveConnKey = dbFactory::getConnKey($slaverDbConfig['dsn']);
//		dbFactory::$dbNodeConns[$slaveConnKey] = &$dbSlave;
	}
	else
	{
		$dbSlave = &$db;
	}
}

// 功能表定位函数
function get_table_idx($combatId)
{
	$combatId = strval($combatId);
	$prefix = substr($combatId, 0, 4);
	$idx = $prefix % 10;
	return $idx;
}

// 生成 ID 前缀
function make_id_prefix($combatTime)
{
	$prefix = (date('n') + 10) . date('d', $combatTime);
	return $prefix;
}

// 获取哈希后第一个字符
function get_hash_key($string)
{
	$hash = md5($string);
	return $hash[0];
}

class passportDb
{
	static $db = null;
	static public function create()
	{
		if (empty(self::$db))
		{
			global $dbPassportConfig;
			self::$db = init_db($dbPassportConfig);
			return self::$db;
		}
		else
		{
			return self::$db;
		}
	}
}

//初始化主数据库
// db_class_init();
$db =stra::db();
?>