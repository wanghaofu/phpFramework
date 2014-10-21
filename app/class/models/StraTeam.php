<?php
/**
 * @name 大战略玩家配对算法
 * $author chenliang
 * $date 2012-02
 */

class StraTeam extends Init 
{
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 编组队列中间操作(新增)
	 * @param $array 
	 * @return $lastId
	 */
	public function teamAdd( $array )
	{
		$i = 1;
		foreach ($array as $arr) {
			$this->_stra_udb->addData("id{$i}", $arr);
			$this->_stra_udb->addData("ctime", time());
			$i++;
		}
		return $this->_stra_udb->dataInsert('stra_teams');
	}
	
	public function teamUpdate( $property, $value, $where )
	{
		$this->_stra_udb->set($property, $value);
		if( is_array($where) ) $where = implode('=', $where);
		$this->_stra_udb->dataUpdate('stra_teams', $where);
	}
	
	/**
	 * 编组队列中间操作(删除)
	 * @param $array 
	 */
	public function teamDel( $id )
	{
		//del...
	}
}
?>