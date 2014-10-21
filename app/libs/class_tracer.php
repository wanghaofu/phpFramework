<?php

/******************************************************************

	Name: 程序追踪调试类
	Author: 王涛 
	Email: wanghaofu@163.com
	QQ: 595900598

/******************************************************************/

/* 示例 :

$tracer = new tracer ();
$tracer->init ( $tableName );
$tracer->trace ( $tag );
$trace->close ();

*/

if ( !defined ( 'IN_SYSTEM' ) )
{
	exit ( 'Access Denied' );
}

class tracer
{
	var $conn;
	var $traceId = 0;
	var $status = 0;
	var $traceDB = array (
		'server' => '',
		'username' => '',
		'password' => '',
		'database' => 'debug',
		);
	var $traceTable = 'debug';

	// 构造函数
	function tracer ( $note = null, $tableName = null )
	{
		$this->init ( $note, $tableName );
	}

	// 连接数据库
	function connectDB ()
	{
		if ( !$this->conn )
		{
			$this->conn = @mysql_connect ( $this->traceDB['server'], $this->traceDB['username'], $this->traceDB['password'] );
			@mysql_select_db ( $this->traceDB['database'], $this->conn );
		}
		return $this->conn;
	}

	// 初始化
	function init ( $note = null, $tableName = null )
	{
		if ( !is_null ( $tableName ) )
		{
			$this->traceTable = $tableName;
		}

		$this->connectDB ();
		@mysql_query ( "insert into {$this->traceTable} ( client_ip, server_ip, script, init_time, note, status ) values ( '{$_SERVER['REMOTE_ADDR']}', '{$_SERVER['SERVER_ADDR']}', '{$_SERVER[SCRIPT_NAME]}?{$_SERVER[QUERY_STRING]}', " . time () . ", '{$note}', '0' );", $this->conn );
		$this->traceId = @mysql_insert_id ();
	}

	// 记录追踪信息
	function trace ( $tag = null )
	{
		if ( !$tag ) $tag = $this->status ++;

		$this->connectDB ();
		@mysql_query ( "update {$this->traceTable} set status = '$tag' where id = {$this->traceId}", $this->conn );
	}

	// 关闭追踪
	function close ()
	{
		$this->connectDB ();
		@mysql_query ( "delete from {$this->traceTable} where id = {$this->traceId}", $this->conn );
		@mysql_close ( $this->conn );
	}
}
?>