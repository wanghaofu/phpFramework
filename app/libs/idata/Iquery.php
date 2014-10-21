<?php
/******************************************************************

Name: 数据库操作类 接口封装类
Author: 王涛 ( Tony )
Email: wanghaofu@163.com
QQ: 595900598
edit 2008-2
 ****************************************************************/
class Iquery {
	
	var $insData = NULL;
	var $checkpass = TRUE;
	var $errinfo = NULL;
	var $db_insert_id = NULL;
	var $db_debug = FALSE;
	var $db;
	
	function Iquery($db) {
		$this->db = $db;
		$this->db->debug = true;
		$this->checkpass = TRUE;
	}
	/**
	 * for update insert
	 * Enter description here .
	 * ..
	 * 
	 * @param unknown_type $key        	
	 * @param unknown_type $val        	
	 */
	public function set($key, $val = NULL, $quotes = true) {
		if (false != is_array ( $key )) {
			foreach ( $key as $key => $var ) {
				if (is_array ( $var )) {
					continue;
				}
				$var = $this->setValTag ( $val, $quotes );
				$this->insData [$key] = $val;
			}
		} else {
			if (is_array ( $val )) {
				continue;
			}
			$val = $this->setValTag ( $val, $quotes );
			$this->insData [$key] = $val;
		}
	}
	private function setValTag($val, $quotes) {
		
		$val = addslashes ( $val );
		if ($quotes == false) {
			return $val = array (
					'val' => $val,
					'quote' => false
			);
		} else {
			return $val = array (
					'val' => $val,
					'quote' => true
			);
		}
	}
	private function setVal($val) {
		if (is_array ( $val )) {
			$value = $val ['val'];
		} else {
			$value = $val;
		}
		if (  empty( $val['quote'] )) {
			return $value;
		} else {
			return "'{$value }'";
			
		}
	}
	/**
	 * alies
	 * Enter description here .
	 * ..
	 * @param unknown_type $key        	
	 * @param unknown_type $val        	
	 */
	public function addData($key, $val) {
		$this->set ( $key, $val );
	}
	
	public function chgData($key, $val) {
		$this->insData [$key] = $val;
	}
	
	function delData($key) {
		unset ( $this->insData [$key] );
	}
	
	function flush() {
		$this->insData = array ();
	}
	
	public function dataInsert($Table) {
		if (empty ( $this->checkpass )) {
			return false;
		} else {
			$insData_Num = count ( $this->insData );
			$Foreach_I = 0;
			$query = "Insert into " . $Table . " (";
			$query_key = "";
			$query_val = "";
			
			foreach ( $this->insData as $key => $val ) {
				if (is_array ( $val )) {
					$chkval = $val ['val'];
				} else {
					$chkval = $val;
				}
				if (0 < strlen ( $chkval )) {
					if ($Foreach_I == 0) {
						$query_key .= "`" . $key . "`";
						$query_val .= $this->setVal ( $this->ensql ( $val ) );
					} else {
						$query_key .= ",`" . $key . "`";
						$query_val .= "," . $this->setVal ( $this->ensql ( $val ) );
					}
					$Foreach_I += 1;
				}
			}
			$query .= $query_key . ") Values (" . $query_val . ")";
			$this->flush ();
			$result = $this->db->query ( $query );
			if ($result) {
				$this->db_insert_id = $this->db->lastInsertId (); //取插入的主键ID值。 
				return $this->db_insert_id ?:true;  //如果为真返回主键
				
			} else {
				$result = $this->db->errorMsg ();
				$this->db->errorinfo [] = "<P>数据库错误:数据更新失败。MySQL_ERRNO:" . $result [code] . ". MySQL_ERROR:" . $result [message] . "</P>";
				$this->db->report = $query;
				File::debug_log ( $this->db->errorMsg () );
				return FALSE;
			}
		}
	
	}
	
	public function dataUpdate($table, $where='') {
		if (! $this->checkpass) {
			return 0;
		} else {
			$Foreach_I = 0;
			$query = "update " . $table . " set ";
			$query_key = "";
			$query_val = "";
			foreach ( $this->insData as $key => $val ) {
				if (is_array ( $val )) {
					$chkval = $val ['val'];
				} else {
					$chkval = $val;
				}
			
				if (0 <= strlen ( $chkval )) {
					if ($Foreach_I == 0) {
						$query_key = "`" . $key . "`";
						$query_val = "=" . $this->setVal ( $this->ensql ( $val ) );
						$query .= $query_key . $query_val;
					} else {
						$query_key = ",`" . $key . "`";
						$query_val = "=" . $this->setVal ( $this->ensql ( $val ) );
						$query .= $query_key . $query_val;
					}
					$Foreach_I += 1;
				}
			}
			if (! empty ( $where )) {
				$query .= " where {$where}";
			}
			$this->flush ();
			if ($this->db->query ( $query )) {
				return TRUE;
			} else {
				$result = $this->db->errorMsg ();
				$this->db->errorinfo [] = "<P>数据库错误:数据更新失败。MySQL_ERRNO:" . $result [code] . ".; MySQL_ERROR:" . $result [message] . "</P>";
				$this->db->report = $query;
				File::debug_log ( $this->db->errorMsg () );
				return FALSE;
			}
		}
	
	}
	
	public function dataReplace($Table) {
		if (! $this->checkpass) {
			return 0;
		} else {
			$insData_Num = count ( $this->insData );
			$Foreach_I = 0;
			$query = "Replace into " . $Table . " (";
			$query_key = "";
			$query_val = "";
			foreach ( $this->insData as $key => $val ) {
				if (is_array ( $val )) {
					$chkval = $val ['val'];
				} else {
					$chkval = $val;
				}
				if (0 < strlen ( $chkval )) {
					if ($Foreach_I == 0) {
						$query_key .= "`" . $key . "`";
						$query_val .= $this->setVal ( $this->ensql ( $val ) );
					} else {
						$query_key .= ",`" . $key . "`";
						$query_val .= "," . $this->setVal ( $this->ensql ( $val ) );
					}
					$Foreach_I += 1;
				}
			}
			$query .= $query_key . ") Values (" . $query_val . ")";
			
			if ($result = $this->db->query ( $query )) {
				// $db_insert_id = $this->db->Insert_ID( );
				$db_insert_id = $this->db->lastInsertId ();
				return TRUE;
			} else {
				$result = $this->db->errorMsg ();
				$this->db->errorinfo [] = "<P>数据库错误:数据更新失败。MySQL_ERRNO:" . $result [code] . ".&nbsp;&nbsp;MySQL_ERROR:" . $result [message] . "</P>";
				$this->db->report = $query;
				File::debug_log ( $this->db->errorMsg () );
				return FALSE;
			}
		}
	}
	
	public function dataDel($table, $which, $id, $method = "=") {
		if (! $this->checkpass) {
			return 0;
		} else {
			$query = "Delete From " . $table . " where " . $which . $method . $id;
			if ($this->db->query ( $query )) {
				return TRUE;
			} else {
				$result = $this->db->errorMsg ();
				$this->db->errorinfo [] = "<P>数据库错误:数据更新失败。MySQL_ERRNO:" . $result [code] . ".&nbsp;&nbsp;MySQL_ERROR:" . $result [message] . "</P>";
				$this->db->report = $query;
				File::debug_log ( $this->db->errorMsg () );
				return FALSE;
			}
		}
	}
	/* 扩展数据插入 根据提供的数组自动插入数据 */
	public function autoDataInsert($tableName, $IN = '') {
		if (false == $this->repareData ( $tableName, $IN )) {
			return false;
		}
		$res = $this->dataInsert ( $tableName );
		return $res;
	}
	/* 扩展数据修改	根据提供的数组自动修改数据数据 */
	public function autoDataUpdate($tableName, $where = '', $IN = '') {
		if (! $where)
			return false;
		
		if (false == $this->repareData ( $tableName, $IN )) {
			return false;
		}
		$res = $this->dataUpdate ( $tableName, $where );
		return $res;
	}
	
	/* 扩展数据修改	根据提供的数组自动修改数据数据 */
	public function autoDataReplace($tableName, $where = '', $IN = '') {
		// if ( false == $where ) return false;
		if (false == $this->repareData ( $tableName, $IN )) {
			return false;
		}
		$res = $this->dataReplace ( $tableName, $where );
		return $res;
	}
	public function getForm($tmpArray) {
		foreach ( $tmpArray as $key => $val ) {
			$this->insData [$key] = $val;
		}
	}
	
	public function filterData($IN) {
		if (! is_array ( $IN )) {
			return FALSE;
		}
		foreach ( $IN as $key => $var ) {
			$header = substr ( $key, 0, 5 );
			if ($header == "data_") {
				$field = substr ( $key, 5 );
				if (is_array ( $var )) {
					$tmp_var = "";
					foreach ( $var as $var_key => $value ) {
						if (empty ( $var_key )) {
							$tmp_var = $value;
						} else {
							$tmp_var .= "," . $value;
						}
					}
					$var = $tmp_var;
				}
				$this->addData ( $field, $var );
			} else {
				continue;
			}
		}
	}
	
	public function debugData() {
		foreach ( $this->insData as $key => $val ) {
			echo "{$key} -- {$val} \n<br>";
		}
		exit ();
	}
	
	public function getData($key = NULL) {
		if ($key) {
			return $this->insData [$key];
		} else {
			return $this->insData;
		}
	}
	
	public function dataExists($table, $method, $field, $var) {
		$query = "select COUNT(*) as nr From " . $table . " where " . $field . $method . $var;
		$result = $this->db->query ( $query );
		if ($result) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function ensql($string) {
		return $string;
	}
	
	function chkTel($strPhoneNumber) {
		if (strspn ( $strPhoneNumber, "0123456789-" )) {
			$errinfo [] = "Telphone number input error.";
			$checkpass = FALSE;
		}
	}
	
	function chkStrIsNull($chkStr, $strName) {
		if (0 < ! strlen ( $chkStr )) {
			$this->errinfo [] = $strName . "不能为空.";
			$this->checkpass = FALSE;
		}
	}
	
	/* 扩展数据准备 */
	private function repareData($tableName, $IN = '') {
		if (empty ( $IN )) {
			global $IN;
		}
		if (empty ( $IN )) {
			File::debug_log ( 'fileds is null' );
			return false;
		}
		
		$fields = $this->getFields ( $tableName );
		$this->flush ();
		
		foreach ( $IN as $key => $value ) {
			if (false != in_array ( $key, $fields ) && false == is_numeric ( $key )) { // 确保表中有的数据才会被加入
				$this->addData ( $key, $value );
			}
		}
		return true;
	}
	private function getFields($tableName) {
		
		if (! $tableName)
			return false;
		$fields = $this->db->getRows ( "show full fields from {$tableName}" );
		foreach ( $fields as $key => $field ) {
			$tableFields [] = $field ['Field'];
		}
		return $tableFields;
	}
}

?>
