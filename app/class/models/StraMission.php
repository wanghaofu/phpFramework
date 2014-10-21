<?php
/**
 * @name 大战略模块
 * @access public
 * $author chenliang
 * $date 2012-02
 */

class StraMission extends Init 
{
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 新增大战略作战任务
	 */
	public function newMission( $stra_serial_num )
	{
		for ($i=1;$i<=27;$i++) {
			$mission_id = $stra_serial_num * 100 + $i;
			$sqlValues[$i] = "($mission_id,$stra_serial_num,0,0)";
		}
		$safeLeft = array(1,6,12,17,23);
		$saferight = array(5,11,16,22,27);
		$sql = "SELECT `uuid`,`force` FROM stra_session WHERE stra_serial_num=$stra_serial_num";
		$res = $this->_stra_udb->getRows($sql);
		$initForce = $res[0]['force'];
		foreach ($res as $rs) {
			if($rs['force'] == $initForce) {//同派系
				$safe = &$safeLeft;
			} else {						//反派系
				$safe = &$saferight;
			}
			$key = array_rand($safe);
			$i = $safe[$key];
			$mission_id = $stra_serial_num * 100 + $i;
			$sqlValues[$i] = "($mission_id,$stra_serial_num,1,{$rs['uuid']})";
			unset($safe[$key]);
			unset($safe);
		}
		$sql = "INSERT INTO stra_missions (mission_id,stra_serial_num,issafe,usermark) VALUES ".implode(',', $sqlValues);
		$this->_stra_udb->exec($sql);
		$this->_stra_uCache->flush('straMissions');
	}
	
	/**
	 * 修改大战略任务中的属性
	 * @param $mission_id 任务ID
	 * @param $properties 属性名
	 * @param $value 属性值
	 * @param $isChange	是否为增幅值
	 */
	public function updateMission( $mission_id, $properties, $value, $isChange = false )
	{
		if( !$isChange ) {
			$this->_udb->set($properties, $value);
		} else {
			$this->_udb->set($properties, "$properties+$value", false);
		}
		$this->_udb->dataUpdate('user_missions', "uuid=$this->uuid AND mission_id=$mission_id");
		$this->_uCache->flush('userMissions');
	}
}
?>