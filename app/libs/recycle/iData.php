<?php
/******************************************************************

Name: 数据库操作类 ( 基于 PDO )
Author: 王涛 ( Tony )
Email: wanghaofu@163.com
QQ: 595900598
edit 2008-2
****************************************************************/
class iData
{

	var $insData = NULL;
	var $checkpass = TRUE;
	var $errinfo = NULL;
	var $db_insert_id = NULL;
	var $db_debug = FALSE;
	var $db;
	
	function iData( $db='' )
	{
		if ( '' == $db ){
			global $db;
			$this->db = $db;
		}else {
			$this->db = $db;
		}
		if ( false == $this->db ) {
			return false;
		}
		if ( false != $this->db_debug ) {
			$this->db->debug = true;
		}
		$this->checkpass = TRUE;
	}
	function setDb( $db ='' )
	{
		if ( false != $db ) {
			$this->db = $db;
		} else {
			return false;
		}
	}


	function getForm( $tmpArray )
	{
		foreach ( $tmpArray as $key => $val )
		{
			$this->insData[$key] = $val;
		}
	}

	function filterData( $IN )
	{
		if ( !is_array( $IN ) )
		{
			return FALSE;
		}
		foreach ( $IN as $key => $var )
		{
			$header = substr( $key, 0, 5 );
			if ( $header == "data_" )
			{
				$field = substr( $key, 5 );
				if ( is_array( $var ) )
				{
					$tmp_var = "";
					foreach ( $var as $var_key => $value )
					{
						if ( empty( $var_key ) )
						{
							$tmp_var = $value;
						}
						else
						{
							$tmp_var .= ",".$value;
						}
					}
					$var = $tmp_var;
				}
				$this->addData( $field, $var );
			}
			else
			{
				continue;
			}
		}
	}

	function debugData( )
	{
		foreach ( $this->insData as $key => $val )
		{
			echo "{$key} -- {$val} \n<br>";
		}
		exit( );
	}

	function getData( $key = NULL )
	{
		if (  $key  )
		{
			return $this->insData[$key];
		}
		else
		{
			return $this->insData;
		}
	}

	function addData( $key, $val = NULL )
	{
		if ( false != is_array( $key ) )
		{
			foreach ( $key as $key => $var )
			{
				if ( is_array( $var ) )
				{
					continue;
				}
				$this->insData[$key] = addslashes( $var );
			}
		}
		else
		{
			if ( is_array( $val ) )
			{
				continue;
			}
			//			$this->insData[$key] = $this->db->escape_string( $val );
			$this->insData[$key] = addslashes( $val );
		}
	}

	function delData( $key )
	{
		unset( $this->insData[$key] );
	}
	
	function clean(  )
	{
		$this->flushData();
	}

	function flushData( )
	{
		$this->insData = array();
	}

	private	function chgData( $key, $val )
	{
		$this->insData[$key] = $val;
	}

	public	function dataInsert( $Table )
	{
		if ( empty($this->checkpass) )
		{
			return false;
		}
		else
		{
			$insData_Num = count( $this->insData );
			$Foreach_I = 0;
			$query = "Insert into ".$Table." \n(\n";
			$query_key = "";
			$query_val = "";

			foreach ( $this->insData as $key => $val )
			{
				if ( 0 < strlen( $val ) )
				{
					if ( $Foreach_I == 0 )
					{
						$query_key .= "`".$key."`";
						$query_val .= "'".$this->ensql( $val )."'";
					}
					else
					{
						$query_key .= ",\n`".$key."`";
						$query_val .= ",\n'".$this->ensql( $val )."'";
					}
					$Foreach_I += 1;
				}
			}
			$query .= $query_key."\n) \nValues \n(\n".$query_val."\n)";
			//			echo $query.__file__.__line__.'<br/>';
			 $result = $this->db->query( $query ) ;
			if (  $result )
			{
				$this->db_insert_id = $this->db->lastInsertId( );
				return TRUE;
			}
			else
			{
				$result = $this->db->errorMsg( );
				$this->db->errorinfo[] = "<P>数据库错误:数据更新失败。MySQL_ERRNO:".$result[code].". MySQL_ERROR:".$result[message]."</P>";
				$this->db->report = $query;
				@_debug_log(  $this->db->errorMsg( ) );
				return FALSE;
			}
		}
	}

	public	function dataUpdate( $table, $where )
	{
		if ( !$this->checkpass )
		{
			return 0;
		}
		else
		{
			$Foreach_I = 0;
			$query = "update ".$table." set ";
			$query_key = "";
			$query_val = "";
			foreach ( $this->insData as $key => $val )
			{
				if ( 0 <= strlen( $val ) )
				{
					if ( $Foreach_I == 0 )
					{
						$query_key = "`".$key."`";
						$query_val = "='".$this->ensql( $val )."'";
						$query .= $query_key.$query_val;
					}
					else
					{
						$query_key = ",`".$key."`";
						$query_val = "='".$this->ensql( $val )."'";
						$query .= $query_key.$query_val;
					}
					$Foreach_I += 1;
				}
			}
			$query .= "where {$where}";
			if ( $this->db->query( $query ) )
			{
				return TRUE;
			}
			else
			{
				$result = $this->db->errorMsg( );
				$this->db->errorinfo[] = "<P>数据库错误:数据更新失败。MySQL_ERRNO:".$result[code].".; MySQL_ERROR:".$result[message]."</P>";
				$this->db->report = $query;
				@_debug_log( $this->db->errorMsg( ) );
				return FALSE;
			}
		}
	}

	public	function dataReplace( $Table )
	{
		if ( !$this->checkpass )
		{
			return 0;
		}
		else
		{
			$insData_Num = count( $this->insData );
			$Foreach_I = 0;
			$query = "Replace into ".$Table." \n(\n";
			$query_key = "";
			$query_val = "";
			foreach ( $this->insData as $key => $val )
			{
				if ( 0 < strlen( $val ) )
				{
					if ( $Foreach_I == 0 )
					{
						$query_key .= "`".$key."`";
						$query_val .= "'".$this->ensql( $val )."'";
					}
					else
					{
						$query_key .= ",\n`".$key."`";
						$query_val .= ",\n'".$this->ensql( $val )."'";
					}
					$Foreach_I += 1;
				}
			}
			$query .= $query_key."\n) \nValues \n(\n".$query_val."\n)";


			if ( $result = $this->db->query( $query ) )
			{
				//				$db_insert_id = $this->db->Insert_ID( );
				$db_insert_id = $this->db->lastInsertId( );
				return TRUE;
			}
			else
			{
				$result = $this->db->errorMsg( );
				$this->db->errorinfo[] = "<P>数据库错误:数据更新失败。MySQL_ERRNO:".$result[code].".&nbsp;&nbsp;MySQL_ERROR:".$result[message]."</P>";
				$this->db->report = $query;
				@_debug_log(  $this->db->errorMsg( ) );
				return FALSE;
			}
		}
	}

	public	function dataDel( $table, $which, $id, $method = "=" )
	{
		if ( !$this->checkpass )
		{
			return 0;
		}
		else
		{
			$query = "Delete From ".$table." where ".$which.$method.$id;
			if ( $this->db->query( $query ) )
			{
				return TRUE;
			}
			else
			{
				$result = $this->db->errorMsg( );
				$this->db->errorinfo[] = "<P>数据库错误:数据更新失败。MySQL_ERRNO:".$result[code].".&nbsp;&nbsp;MySQL_ERROR:".$result[message]."</P>";
				$this->db->report = $query;
				@_debug_log(  $this->db->errorMsg( ) );
				return FALSE;
			}
		}
	}

	public	function dataExists( $table, $method, $field, $var )
	{
		$query = "select COUNT(*) as nr From ".$table." where ".$field.$method.$var;
		$result = $this->db->Execute( $query );
		if ( $result )
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function ensql( $string )
	{
		return  $string ;
	}

	function chkTel( $strPhoneNumber )
	{
		if ( strspn( $strPhoneNumber, "0123456789-" ) )
		{
			$errinfo[] = "Telphone number input error.";
			$checkpass = FALSE;
		}
	}

	function chkStrIsNull( $chkStr, $strName )
	{
		if ( 0 < !strlen( $chkStr ) )
		{
			$this->errinfo[] = $strName."不能为空.";
			$this->checkpass = FALSE;
		}
	}

	private function getFields( $tableName ){

		if ( !$tableName ) return false;
		$fields = $this->db->getRows( "show full fields from `{$tableName}");
		foreach( $fields as $key=>$field ){
			$tableFields[] = $field['Field'];
		}
		return $tableFields;
	}
//	/*扩展数据准备*/
//	private	function repareData ( $tableName ,$IN='' ){
//		if ( empty( $IN ) 	){
//			global $IN;
//		}
//		if( empty($IN) )
//		{
//			_debug_log('fileds is null');
//			return false;
//		}
//		
//		$fields = $this->getFields($tableName);
//		$this->flushData();
//
//		foreach( $IN as $key=>$value  ){
//			if ( false != in_array( $key , $fields ) && false == is_numeric( $key ) ){  //确保表中有的数据才会被加入
//				$this->addData( $key,$value );
//			}
//		}
//		return true;
//	}
//	/*扩展数据插入 根据提供的数组自动插入数据	*/
//	public	function tblDataAdd ( $tableName , $IN='' )
//	{
//		if ( false == $this->repareData( $tableName , $IN ) )
//		{
//			return false;
//		}
//		$res = $this->dataInsert( $tableName );
//		return $res;
//	}
//	/*扩展数据修改	根据提供的数组自动修改数据数据*/
//	public	function tblDataModify ( $tableName ,$where='' , $IN =''){
//		if ( !$where ) return false;
//
//		if ( false == $this->repareData( $tableName , $IN ) )
//		{
//			return false;
//		}
//		$res = $this->dataUpdate( $tableName ,$where );
//		return $res;
//	}
//
//	/*扩展数据修改	根据提供的数组自动修改数据数据*/
//	public	function tblDataReplace ( $tableName , $where='' , $IN =''){
//		//		if ( false == $where ) return false;
//		if ( false == $this->repareData( $tableName , $IN ) )
//		{
//			return false;
//		}
//		$res = $this->dataReplace( $tableName , $where );
//		return $res;
//	}
//	

}

?>
