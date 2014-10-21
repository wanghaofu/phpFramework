<?php

/******************************************************************

	Name: 日志操作类
	Author: 王涛 
	Email: wanghaofu@163.com
	QQ: 595900598

/******************************************************************/

if ( !defined ( 'IN_SYSTEM' ) )
{
	exit ( 'Access Denied' );
}

class Log
{
	var $db; // 日志数据库类
	var $logSettings; // 日志配置
	var $tableSuffix = '_Ym'; // 日志表名后缀，将经过 date() 函数处理
	var $ignoreError = false; // 忽略错误
	var $disabled = false; // 关闭标志

	// 构造函数
	function clsLog (db $dbLog, $tableSuffix = null )
	{
		$this->db = $dbLog;

		if ( $tableSuffix != null ) $this->tableSuffix = $tableSuffix;

		$this->logSettings = array ();
	}

	/******* 添加日志配置 *******

	$key : 日志配置键名
	$table : 数据库表名
	$fields: 字段数组，格式为 字段名 => 字段类型
	$preFields: 预定义字段数组，格式为 字段名 => 字段值字符串（用于 eval）
	
	Ex.
	$clsLog->addLogSetting ( 'moneyLog', 'user_economy', array ( 'uuid' => '用户名称', 'change_value' => '变化值', 'status' => '状态' ) );

	**************************/
	function addLogSetting ( $key, $table, $fields, $preFields = null, $tableStructure = null )
	{
		if ( $this->tableSuffix )
		{
			$table .= date ( $this->tableSuffix );
		}

		$this->logSettings[$key] = array (
			'table' => $table,
			'fields' => $fields,
			'preFields' => $preFields,
			'tableStructure' => $tableStructure,
			);
	}

	// 创建字段值
	function makeFieldValues ( $fields )
	{
		$arrValues = array ();
		while ( list ( $key, $item ) = @each ( $fields ) )
		{
			if ( is_array ( $item ) )
			{
				$arrValues = array_merge ( $arrValues, $this->makeFieldValues ( $item ) );
			}
			else
			{
				$arrValues[$key] =  $item;				
			}
		}
		return $arrValues;
	}

	/******* 写入日志 *******

	$key : 日志配置键名
	其他参数: 依次插入 $fields 的值

	Ex.
	$clsLog->writeLog ( 'moneyLog', 1, 100, true );

	**************************/
	function writeLog ( $key )
	{
		if ( $this->disabled || !array_key_exists ( $key, $this->logSettings ) )
		{
			return false;
		}

		$logSetting = $this->logSettings[$key];
		$args = func_get_args ();
		array_shift ( $args );

		// 参数不足
		if ( count ( $args ) < 1 )
		{
			$fields = array ();
			while ( list ( $fieldKey, $item ) = @each ( $logSetting['fields'] ) )
			{
				array_push ( $fields, "'$fieldKey' => $fieldKey" );
			}
			$fields = join ( ", \n\t\t", $fields );

			echo ( "<pre><b>Call the method as follow:</b>\n" . __FUNCTION__ . " ( '" . $key . "',\n\tarray (\n\t\t" . $fields . "\n\t) );</pre>" );
			exit;
		}

		$logTime = time (); // 日志时间

		$table = $logSetting['table'];
		$arrFields = $arrValues = $extFields = array ();

		// 生成字段
		while ( list ( $fieldKey, $item ) = @each ( $logSetting['fields'] ) )
		{
			array_push ( $arrFields, $fieldKey );
		}

		// 生成字段值
		while ( list ( $fieldKey, $item ) = @each ( $args ) )
		{
			if ( is_array ( $item ) )
			{
				$arrValues = array_merge ( $arrValues, $this->makeFieldValues ( $item ) );
			}
			else
			{
				$arrValues[$arrFields[$fieldKey]] = $value;
			}
		}

		// 添加预定义字段
		while ( list ( $fieldKey, $item ) = @each ( $logSetting['preFields'] ) )
		{
			eval ( "\$value = \"$item\";" );
			$arrValues[$fieldKey] = $value;
		}

		// 创建日志
		$arrSql = array ();
		$arrFields = $logSetting['fields'];
		while ( list ( $fieldName, $fieldWrapFunc ) = @each ( $arrFields ) )
		{
			if ( !@array_key_exists ( $fieldName, $arrValues ) )
			{
				continue;
			}
			$fieldValue = $arrValues[$fieldName];
			if ( $fieldWrapFunc )
			{
				$fieldValue = $fieldWrapFunc ( $fieldValue );
			}
			array_push ( $arrSql, "$fieldName = '$fieldValue'" );
		}
		$strSql = join ( ', ', $arrSql );
		$logSql = "INSERT INTO $table SET $strSql;\n";

		$this->db->query ( $logSql );
		if ( $this->db->affectedRows < 1 )
		{
			$this->db->query ( "CREATE TABLE $table {$logSetting['tableStructure']}" );
			$this->db->query ( $logSql );
		}
		$this->db->commit ();
	}
}
?>