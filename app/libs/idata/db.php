<?php
// namespace data;
/**
 * ****************************************************************
 * Name: 数据库操作类 ( 基于 PDO )
 * Author: 王涛 ( Tony )
 * Email: wanghaofu@163.com
 * QQ: 595900598
 *
 * ****************************************************************
 */

/*
 * 示例 : $db = new db ( 'mysql:host=127.0.0.1;port=3306;', 'root', 'password',
 * 'database_name', true, 'utf8' );
 */

if (! defined ( 'IN_SYSTEM' )) {
	exit ( 'Access Denied' );
}

class db {
	var $id;
	var $dsn;
	var $user;
	var $password;
	var $database;
	
	var $charSet;
	var $ignoreError;
	var $attributes;
	
	var $conn; // 数据库连接
	var $queryCount; // 查询次数
	var $affectedRows; // 影响行数 ( 每次 Query 后改变 )
	var $debug; // 调试模式
	var $debugLineSplit = '<br />';
	var $charSplit = '`';
	var $transaction = false;
	var $inTransaction = false;
	var $readOnly = false; // 是否只读
	
	var $startTime;
	var $endTime;
	var $statSql;
	var $statSqlLimit = 10; // 历史 Query 条数
	
	var $traceEnabled = false;
	var $tracer = null;
	
	var $dbConfigs = array (); // 预备数据库配置
	
	var $dbIdxArray = array ();
	var $multiFlag = false;
	
	var $split_value;
	
	var $dbIdx = null;
	var $tableIdx = null;
	
	var $sqlCallFun = array ();
	
	var $writeOperations = array (
			'ALTER ',
			'CREATE ',
			'DROP ',
			'DELETE ',
			'INSERT ',
			'REPLACE ',
			'TRUNCATE ',
			'UPDATE ' 
	); // 定义写操作
	
	static $iquery = array ();
	var $nodeQuery = false;
	static $dbNodeConns = array ();
	
	// 初始化数据库配置
	function db($dsn, $user = '', $password = '', $database = null, $autoCommit = false, $charSet = null, $persistent = false, $ignoreError = false, $timeout = 10) {
		if (is_array ( $dsn )) {
			$this->db_multi ( $dsn );
		} else {
			$this->dsn = $dsn;
			$this->user = $user;
			$this->password = $password;
			$this->database = $database;
			$this->charSet = $charSet;
			$this->ignoreError = $ignoreError;
			
			$this->id = md5 ( $this->dsn . $this->user . $this->database );
			
			$this->attributes = array (
					PDO::ATTR_AUTOCOMMIT => $autoCommit,
					PDO::ATTR_PERSISTENT => $persistent,
					PDO::ATTR_TIMEOUT => $timeout 
			);
			
			$this->conn = null;
			$this->queryCount = 0;
			$this->startTime = '';
			$this->statSql = array ();
			$this->endTime = '';
		}
	}
	
	function setSplitValue($value)
	{
		$this->split_value = $value;
	}
	function setDatabase($database) {
		$this->database = $database;
	}
	
	// 初始化随极数据库配置
	function db_multi($dbConfigs = null) {
		$this->multiFlag = true;
		
		if (is_array ( $dbConfigs )) {
			$this->dbConfigs = $dbConfigs;
			$this->dbIdxArray = array_keys ( $this->dbConfigs );
			shuffle ( $this->dbIdxArray );
		}
		
		$dbIdxSession = $_COOKIE ['db_idx_session']; // 直接获取用户位置
		if (is_numeric ( $dbIdxSession )) 		//
		{
			$this->dbIdx = intval ( $dbIdxSession );
		} else {
			$this->dbIdx = array_shift ( $this->dbIdxArray );
		}
		
		$dbConfig = $this->dbConfigs [$this->dbIdx];
		$this->db ( $dbConfig ['dsn'], $dbConfig ['user'], $dbConfig ['password'], $dbConfig ['database'], $dbConfig ['auto_commit'], $dbConfig ['charset'], $dbConfig ['persistent'] );
	}
	
	// 连接数据库
	function connect() {
		$dsn = $this->dsn;
		$connKey = self::getConnKey ( $dsn );
		if ($this->nodeQuery == true) {
			if (array_key_exists ( $connKey, self::$dbNodeConns )) {
				$conn = self::$dbNodeConns [$connKey];
				$this->conn = &$conn;
			}
		}
		
		$conn = &$this->conn;
		
		if (! $conn) {
			if ($this->traceEnabled) {
				if (! $this->tracer) {
					include_once ('./libs/idata/tracer.php'); // 暂时关闭执行追踪日志
					$this->tracer = new tracer ( $this->dsn . $this->database );
				}
			}
			try {
				$conn = new PDO ( $dsn, $this->user, $this->password, $this->attributes );
				self::$dbNodeConns [$connKey] = $conn;
				if ($conn && $this->multiFlag)
					setcookie ( 'db_idx_session', $this->dbIdx );
			} catch ( PDOException $e ) {
				if (count ( $this->dbIdxArray ) > 0 && $this->multiFlag) {
					setcookie ( 'db_idx_session', null );
					$_COOKIE ['db_idx_session'] = null;
					
					$this->db_multi ();
					$this->connect ();
				} elseif (! $this->ignoreError) {
					File::debug_log ( $e );
					echo ("<ERROR><div style='padding:20px;'>服务器忙，请稍后访问！</div>");
					throw ($e);
				}
			}
			
			$this->useDatabase ( $this->database, $conn );
			if ($this->charSet && $conn) {
				$conn->query ( "set names '{$this->charSet}';" );
			}
			if ($this->transaction) {
				$this->begin ();
			}
		}
		return $conn;
	}
	function setNode($tag = true) {
		$this->nodeQuery = $tag;
	}
	public static function getConnKey($dsn) {
		
		$connkey = md5 ( $dsn );
		return $connkey;
	}
	
	// 关闭连接
	function close() {
		if ($this->traceEnabled && $this->tracer) {
			$this->tracer->close ();
		}
		$this->inTransaction = false;
		$this->conn = null;
	}
	
	// 使用数据库
	function useDatabase($database, $conn = null) {
		if (! $conn)
			$conn = $this->conn;
		if (! $conn)
			return false;
		if ($database) {
			$conn->query ( "USE $database;" );
			return ! $conn->errorCode () ? true : false;
		}
	}
	
	// 开始事务
	function begin() {
		if (! $this->conn)
			return false;
		if (! $this->inTransaction) {
			$this->conn->beginTransaction ();
			$this->inTransaction = true;
		}
	}
	
	// 提交事务
	function commit() {
		if (! $this->conn)
			return false;
		if ($this->inTransaction) {
			$this->conn->commit ();
			$this->inTransaction = false;
			$this->begin ();
		}
		if ($this->debug) {
			$this->showDebug ();
		}
	}
	
	// 回滚事务
	function rollBack() {
		if (! $this->conn)
			return false;
		if ($this->inTransaction) {
			$this->conn->rollBack ();
			$this->inTransaction = false;
			$this->begin ();
		}
	}
	
	// 输出调试 SQL
	function showDebug() {
		$this->endTime = array_sum ( explode ( ' ', microtime () ) );
		
		while ( list ( $key, $item ) = @each ( $this->statSql ) ) {
			echo $item . $this->debugLineSplit;
		}
		echo '<br />Query Time: ' . ($this->endTime - $this->startTime) . $this->debugLineSplit;
		if ($this->conn && $this->conn->errorCode () > 0) {
			$errorInfo = $this->conn->errorInfo ();
			echo 'Error: ' . $this->conn->errorCode () . ' - ' . $errorInfo [2] . $this->debugLineSplit;
		}
		echo $this->debugLineSplit;
		
		$this->startTime = '';
		$this->endTime = '';
		$this->statSql = array ();
	}
	
	// 统计符合条件的记录条数
	function count($dbTable, $condition = '', $fields = '*') {
		if ($condition != '') {
			$condition = "WHERE $condition";
		}
		$strSql = " SELECT COUNT($fields) AS count_records FROM $dbTable $condition";
		$res = $this->query ( $strSql );
		if ($res) {
			$countRecords = $res->fetch ( PDO::FETCH_ASSOC );
			return $countRecords ['count_records'];
		} else {
			return false;
		}
	}
	
	// 提交数据库查询
	function query($strSql) 
	{
		
		$strSql = $this->execSqlCallBack ( $strSql );
		$strSql = trim ( $strSql );
		
		if ($this->debug) {
			File::debug_log ( $strSql );
		}
		if ($this->readOnly) {
			$writeOperations = $this->writeOperations;
			while ( list ( $key, $item ) = @each ( $writeOperations ) ) {
				if (preg_match ( "/^$item/is", $strSql )) {
					$this->affectedRows = 1;
					return true;
				}
			}
		}
		
		$conn = $this->connect ();
		if (! $conn)
			return false;
		
		$this->queryCount ++; // 查询次数增加
		$this->affectedRows = 0; // 重置影响行数
		
		if (empty ( $this->startTime )) {
			$this->startTime = array_sum ( explode ( ' ', microtime () ) );
		}
		
		array_push ( $this->statSql, $strSql );
		if (count ( $this->statSql ) > $this->statSqlLimit)
			array_shift ( $this->statSql );
		try {
			$res = $conn->query ( $strSql );
		} catch ( PDOException $e ) {
			throw new Exception("SQL_ERROR: $strSql !");
			File::debug_log ( $e );
			
			if (! $this->ignoreError) {
				echo ("<ERROR><div style='padding:20px;'>服务器忙，请稍后访问！</div>");
			}
		}
		
		if ($conn->errorCode () > 0)
			$res = false;
		
		if (! $res) {
			$errorInfo = $conn->errorInfo ();
			throw new Exception($errorInfo [2]);
// 			File::debug_log ( $errorInfo [2] );
			if ($this->debug) {
				echo ('Error: ' . $errorInfo [2] . '<br /><br />SQL: ' . $strSql);
			}
		} else {
			$this->affectedRows += intval ( $res->rowCount () );
		}
		return $res;
	}
	public function getLastId() {
		return $this->lastInsertId ();
	}
	
	// 最后插入 ID
	function lastInsertId() {
		if (! $this->conn)
			return false;
		$lastInsertId = intval ( $this->conn->lastInsertId () );
		return $lastInsertId;
	}
	
	// 获取错误代码
	function errorCode($conn = null) {
		if (! $conn)
			$conn = $this->conn;
		if (! $conn)
			return false;
		$errorCode = intval ( $conn->errorCode () );
		return $errorCode;
	}
	
	// 获取错误信息
	function errorMsg($conn = null) {
		if (! $conn)
			$conn = $this->conn;
		if (! $conn)
			return false;
		$errorInfo = $conn->errorInfo ();
		$errorMsg = $errorInfo [2];
		return $errorMsg;
	}
	/**
	 * Display an error message
	 *
	 * @access public
	 * @param
	 *        	string	the error message
	 * @param
	 *        	string	any "swap" values
	 * @param
	 *        	boolean	whether to localize the message
	 * @return string the application/error_db.php template
	 */
	function display_error($error = '', $swap = '', $native = FALSE) {
		File::debug_log ( $error );
	}
	

	/**
	 * $callBackFunc array(obj,method)
	 * Enter description here .
	 * ..
	 * 
	 * @param unknown_type $callBackFun        	
	 */
	public function setSqlCallFun($callBackFun) {
		$this->sqlCallFun = $callBackFun;
	}
	
	
	/**
	 *
	 *
	 * 执行sql回调
	 * 
	 * @param unknown_type $sql        	
	 */
	function execSqlCallBack($sql) {
		if ($this->sqlCallFun) {
			$sql = call_user_func ( $this->sqlCallFun, $sql );
		}
		return $sql;
	
	}
	
	
	// 执行sql delete updata insert 返回影响行数
	public function exec($sql, $commit = true) {
		$sql = $this->execSqlCallBack ( $sql );
		if (! $this->conn) {
			$this->conn = $this->connect ();
		}
		$this->statSql [] = $sql;
		$res = $this->conn->exec ( $sql );
		if ($commit == true) {
			$this->commit ();
		}
		return $res;
	}
	public function getRow($sql) {
		$sql .= ' limit 1';
		$stmt = $this->query ( $sql );
		if (! $stmt)
			return false;
		$result = $stmt->fetch ( PDO::FETCH_ASSOC );
		return $result;
	}
	// 获取多行
	public function getRows($sql) {
		$stmt = $this->query ( $sql );
		if (! $stmt) {
			return false;
		}
		$result = $stmt->fetchAll ( PDO::FETCH_ASSOC );
		return $result;
	}
	function __call($method, $arguments) {
		switch ($method) {
			
			case 'set' :
			case 'addData' :
			case 'flush' :
			case 'dataInsert' :
			case 'dataUpdate' :
			case 'dateReplace' :
			case 'autoDataInsert' :
			case 'autoDataUpdate' :
			case 'autoDateReplace' :
			case 'dataDel' :
				$class = 'Iquery';
				break;
			case 'update' :
			case 'insert' :
			case 'replace' :
			case 'delete' :
			case 'get' :
			case 'select' :
				
				$class = 'Wquery';
				break;
		}
		
		if (empty ( $class )) {
			throw new Exception ( "$method method is not in query method in db query class group !please check Cquery and Wquery have you's method!" );
		}
		// $dbName = $this->database;
		$key = md5 ( $this->dsn . $class . $this->database . $this->split_value );
		if (empty ( self::$iquery [$key] )) {
			$queryObj = new $class ( $this );
			self::$iquery [$key] = $queryObj;
		} else {
			$queryObj = self::$iquery [$key];
		}
		$result = call_user_func_array ( array (
				$queryObj,
				$method 
		), $arguments );
		return $result;
	}

}
?>