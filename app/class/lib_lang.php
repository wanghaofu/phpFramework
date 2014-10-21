<?php
include_once ('./core/libs/lang/lang.php');
class lib_lang extends lang {
	static $order_id = 0;
	function __construct($lang) {
		parent::__construct ( $lang );
	}
	/**
	 * 生成新的语言包数据
	 *
	 **/
	public function newLanguagePage() {
		$res = $this->checkLangExist ();
		if (empty ( $res )) {
			$this->createLanguageTable ();
		} else {
			$this->createLanguageTmpTable ();
			$this->dropLanguageTable ();
			$this->createLanguageTable ();
		}
		$langConf = $this->getLanguageConf ();
		foreach ( $langConf as $key => $value ) {
			$fields = $this->database->formatStr ( $value ['fields'] );
			if (! $this->database->checkTableExist ( $value ['table_name'] )) {
				$this->error [] = "{$value['table_name']} is not exist ";
				continue;
			}
			try {
				$this->checkLangTableConfFields ( $value ['table_name'], $value ['fields'] );
			} catch ( LANG_EXCEPTION $e ) {
				echo $e->getMessage ();
			}
			$sql = "select {$fields},`{$value['key']}` from {$value['table_name']} order by {$value['key']} asc";
			$data = $this->db->getRows ( $sql );
			$this->insertLangData ( $data, $value );
		}
		$this->dropLanguageTmpTable ();
	}
	private function insertLangData($data, $langConf) {
		foreach ( $data as $key => $value ) {
			$this->insertLangSql ( $value, $langConf );
		}
		$this->db->commit ();
	}
	
	/**
	 * 插入单挑数据
	 *
	 * @param unknown_type $data 单条代翻译数据
	 * @param unknown_type $langConf
	 */
	private function insertLangSql($data, $langConf) {
		$fieldsArr = explode ( ',', $langConf ['fields'] );
		$insertData = array ();
		foreach ( $fieldsArr as $value ) {
			$langKey = $this->getLangKey ( $langConf ['table_name'], $value, $data [$langConf ['key']] );
			$langKeyExistsData = $this->checkLangKeyExists ( $langKey );
			//			self::$order_id = self::$order_id + 1;
			//			self::$order_id = $langConf ['key'];
			$prepareData = array ('package' => $langConf ['table_name'], 'name' => $langKey, 'cn' => $data [$value], 'value' => $langKeyExistsData ['value'] );
			$prepareData ['old_cn'] = $langKeyExistsData ['old_cn'];
			$prepareData ['old_value'] = $langKeyExistsData ['old_value'];
			
			if ($langKeyExistsData && $langKeyExistsData ['cn'] != $data [$value]) {
				$this->execInfo [] = "Table <b> $this->lang_table </b> name <b>$langKey</b> is exists , update cn {$data[$value]}!";
				$where = " name = '$langKey'";
				
				$this->db->insert ( $this->lang_table, $where, $prepareData );
			} else {
				$this->execInfo [] = "Insert new $langKey</b>!";
				$this->db->insert ( $this->lang_table, $prepareData );
			}
		}
	}
	function checkLangKeyExists($langKey) {
		$sql = "select `name`,`cn`,`value` from $this->lang_table_tmp where `name`= '$langKey' ";
		$data = $this->db->getRow ( $sql );
		if ($data)
			return $data;
		else
			return false;
	}
	public function getLanguageTmpTableName() {
		return $this->lang_table_tmp;
	}
	public function getLanguageTableName() {
		return $this->lang_table;
	}
	function getLanguageConf() {
		return $this->db->getRows ( "select * from lang_config" );
	}
	public function checkLangExist() {
		$tables = $this->database->getTable ();
		$table_name = $this->lang_table;
		if (in_array ( $table_name, $tables )) {
			return ture;
		} else {
			return false;
		}
	}
	
	public function unLoadLang() {
		$languageTableName = $this->getLanguageTableName ();
		try {
			$this->deleteLangPackageRecored ();
			$this->dropLanguageTable ( $languageTableName );
		} catch ( Exception $e ) {
			$this->db->rollback ();
		}
		$this->db->commit ();
		$this->execInfo [] = "drop $languageTableName table success";
		return true;
	}
	private function deleteLangPackageRecored() {
		$this->db->delete ( 'lang', "`lang`='{$this->lang}'" );
	}
	private function dropLanguageTable($languageTableName) {
		if (empty ( $languageTableName ))
			$languageTableName = $this->lang_table;
		$this->db->exec ( "drop table $languageTableName " );
	}
	public function createLanguageTable() {
		$sql = 'CREATE TABLE `' . $this->lang_table . "` (
        `order_id` INTEGER(10) UNSIGNED NOT NULL  AUTO_INCREMENT COMMENT '排序',
  		`name` varchar(40) NOT NULL COMMENT '字符串 英文句号分隔 .  表名.主键值.字段 单独的语言信息则单独插入从lang读入',
  		`cn` text COMMENT '中文',
  		`value` text COMMENT '显示文字',
  		`old_cn` text COMMENT '原始中文',
  		`old_value` text COMMENT '原始翻译',
  		`package` varchar(40) NOT NULL DEFAULT 'global' COMMENT '语言包',
 		 PRIMARY KEY (`order_id`),
 		 KEY `name` (`name`),
 		 KEY `language` (`package`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统语言 配置表' ";
		$this->db->exec ( $sql );
	}
	//	private function dropLanguageTable()
	//	{
	//		$this->db->exec( "drop table $this->lang_table " );
	//	}
	private function getTmpLanguage() {
		return $this->db->getRows ( "select * from {$this->lang_table}" );
	}
	private function createLanguageTmpTable() {
		$this->db->exec ( "create table $this->lang_table_tmp select * from {$this->lang_table}" );
	}
	private function dropLanguageTmpTable() {
		$this->db->exec ( "drop table $this->lang_table_tmp" );
	}
	function checkLangTableConfFields($tableName, $fieldStr) {
		$fieldArr = explode ( ',', $fieldStr );
		foreach ( $fieldArr as $value ) {
			$res = $this->database->checkTablesFields ( $tableName, $value ); //检测语言包的字段是否存在
			if (empty ( $res )) {
				$this->error [] = "Language config table <b>$tableName</b> 's Fields <b>$value</b> is not exists!";
				throw new LANG_EXCEPTION ( "Language config table <b>$tableName</b> 's Fields <b>$value</b> is not exists!" );
			} else {
				return true;
			}
			
		}
	}
	function getError() {
		return $this->error;
	}
	function getExecInfo() {
		return $this->execInfo;
	}
	function setDebug() {
		$this->debug = true;
	}
	function __destruct() {
		if ($this->error)
			echo "<br/> <b>Error Config:</b><br/>";
		foreach ( $this->error as $key => $value ) {
			echo $value . '<br/>';
		}
		if (empty ( $this->debug ))
			return;
		if ($this->execInfo)
			echo "<br/><b> ExecInfo is :</b><br/>";
		foreach ( $this->execInfo as $key => $value ) {
			echo $value . '<br/>';
		}
	}
}
?>