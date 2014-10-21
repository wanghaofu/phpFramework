<?php
/**
 * 依赖于 基本系统 
 * @author wangtao
 *
 */
class Index {
	
	static $db;
	
	const APP_ID_TABLE = 'user_id';
	const PLATFORM_ID_TABLE = 'user_name';
	public function __construct() {
		self::$db = self::db();
// 		$this->cache = stra::$cache;
// 		$this->config = $this->cache->getData ( 'config' );
	
	}
	
	static function db($split_value=null) {
		return stra::db ( 'stra_index', $split_value);
	}
	
	
	static function getUuid($userName) {
		if(empty(self::$db))
		{
		self::$db = self::db($userName);
		}
		self::$db->setSplitValue($userName);
		$key = md5($userName);
		$res = self::$db->getRow ( "select uuid from puuid where  md= '{$key}' " );
		return $res['uuid'];
	}
	static public function getPuuid($uuid)
	{
		self::$db->setSplitValue($uuid);
		$res =self::$db->getRow("select puuid from uuid where uuid={$uuid}");
		return $res['puuid'];
	}
	
	static public function initPuuid( $puuid ) {
		$db = self::db (  $puuid );
		if(empty( $puuid ))
		{
			throw new Exception('puuid is not null');
			return false;
		}
		$uuid = self::makeUid ();
		$db->set ( 'puuid', $puuid );
		$db->set ( 'uuid', $uuid );
		$db->set ( 'md', md5( $puuid ) );
		$db->dataInsert ( 'puuid' );
		self::adduuid($uuid,$puuid);
		return $uuid;
	}
	
	static private function adduuid($uuid,$puuid)
	{
		de($uuid);
// 		$this->db->setSplitValue($uuid);
        if(empty( self::$db ) )
        {
        	self::$db = self::db();
        }
		self::$db->setSplitValue($uuid);
		self::$db->set ( 'puuid', $puuid );
		self::$db->set ( 'uuid', $uuid );
		$res = self::$db->dataInsert ( 'uuid' );
        if ( $res )
        {
        	return $res;
        }else{
        	throw new Exception("Add uuid $uuid for $puuid is false");
        }
	}
	
	
	
	private function makeUid() {
		$db = self::$db;
		$db->set ( 'id', 'LAST_INSERT_ID(`id` + 1)', false );
		$db->dataUpdate ( 'user_sequence' );
           $res = $db->getRow('SELECT LAST_INSERT_ID() as id');
		return $res['id'];
	}
	

	
	/**初始化用户 索引表**/
	public function initTable() {
		
		for($i=0;$i<9;$i++)
		{
		$sql =" DROP TABLE IF EXISTS `stra_index`.`puuid_{$i}`;
		CREATE TABLE  `stra_index`.`puuid_{$i}` (
		`md` char(32) NOT NULL COMMENT '平台用户id md5',
		`uuid` varchar(45) NOT NULL COMMENT '应用用户id',
		`puuid` varchar(45) NOT NULL COMMENT 'puuid',
		PRIMARY KEY (`md`) USING BTREE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=REDUNDANT COMMENT='依据平台id获取应用id表';";
		$this->db->exec ( $sql );
		}
		for($i=a;$i<z;$i++)
		{
	    $sql =" DROP TABLE IF EXISTS `stra_index`.`puuid_{$i}`;
		CREATE TABLE  `stra_index`.`puuid_{$i}` (
		`md` char(32) NOT NULL COMMENT '平台用户id md5',
		`uuid` varchar(45) NOT NULL COMMENT '应用用户id',
		`puuid` varchar(45) NOT NULL COMMENT 'puuid',
		PRIMARY KEY (`md`) USING BTREE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=REDUNDANT COMMENT='依据平台id获取应用id表';";
		$this->db->exec ( $sql );
		}
		
		for( $i=1; $i<=100;$i++ )
		{
    		$sql = "DROP TABLE IF EXISTS `stra_index`.`uuid{$i}`;
            CREATE TABLE  `stra_index`.`uuid_{$i}` (
              `uuid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '应用 用户id',
              `puuid` varchar(45) NOT NULL COMMENT '平台用户id',
              PRIMARY KEY (`uuid`) USING BTREE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=REDUNDANT COMMENT='一句应用id获取平台id';";
    		$this->db->exec ( $sql );
		}
		
		de($this->db);
		
		
	}
	
	// 删除索引
	private function remove($puuid) {
		$this->db->delete ( '', "keyValue = {$keyValue}" );
	}
	// 生成 表扩展 core
	private function _getTableExt($value) {
		$value = md5 ( strtolower ( $value ) );
		return $value [0];
	}
	
	private function _getTableName($tableNamePrefix, $keyValue) {
		$tableName = $tableNamePrefix . $this->tableNameSplitor . $this->getTableExt ( $keyValue );
		return $tableName;
	}

}


// class user
// {

// 	function getUserIndexByName($userTag)
// 	{
// 		$this->arithmetic($userTag);

// 	}
// 	function getUserNameByIndex()
// 	{

// 	}
// 	function getUserTableName()
// 	{

// 	}
// 	function getUserDataBaseIndex()
// 	{

// 	}
// 	/**
// 	 * Enter description here...
// 	 *
// 	 * @param unknown_type $userTag username| userid by key for one user;
// 	 */
// 	function arithmetic($userTag)
// 	{
// 		$userMd = md5($userTag);
// 		//    $k = crc32($userTag);
// 		$allUserNum = $this->getGlobalUserInfo();
// 		$dataBaseStep=1000000;
// 		$tableStep= 100000;
// 		$perDatabaseTableNum = ecil($dataBaseStep/$tableStep);
// 		$currentDatabasesStep = ceil($allUserNum/$dataBaseStep); //容量阶段
// 		$currentTableStep = ceil($allUserNum/$tableStep); //容量阶段

// 		/** 一个阶段的库数和表数是定的
// 		 * 1  100
// 		 * 2  200
// 		 * 3  300
// 		 * 4  400
// 		 * 5  500
// 		 * 6  600
// 		 * 7  700
// 		 *
// 		 * database 100万  table 10万
// 		 */
// 		for($step=$currentDatabasesStep;$step>=1; $step--)
// 		{
// 		$userTag = hexdec(substr($userMd,0,2));
// 		$databaseIndex = $Index%$step;
// 		$databasesName = $databaseName.'_'.$databaseIndex;
// 		$tableIndex = $userTag%$perDatabaseTableNum;
// 		$tableName = $tableName.'_'.$index;
// 		//       $userInfo = $db->getRow("select * $databaseName.$tableName where userTag={$userTag}");
// 		if ( $userInfo )
// 		{
// 		break;
// 		}
// 		}
// 		if( empty($userInfo) )
// 		{
// 			$this->addUser();
// 		}


// 		$db = $db->init($databases_index);
// 		$tableName= 'user_'.$table_index;

// 		}
// 		function getGlobalUserInfo()
// 		{
// 		$userNum =1000;
// 		return $userNum;
// 	}

?>
