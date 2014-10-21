<?php
/**
 * @name 部队操作模块
 * @author chenliang $2012-02
 */

class Army extends Init 
{	
	//初始化
	public function __construct( $uuId = 0 )
	{
		parent::__construct( $uuId );
	}
	
	/**
	 * 自动编成
	 * @param $id 部队ID
	 */
	public function autoArmy( $id )
	{
		$army_id = $id;
		global $user;
		$uSoldiers = $user->getUserSoldiers();
		if( !empty($uSoldiers) ) {
			$old = $new = array();
			foreach ($uSoldiers as $sid => $soldier) {
				if($army_id == $soldier['army_id']) {
					$old[] = $sid;
				} else if ($soldier['army_id'] != 0) {
					unset($uSoldiers[ $sid ]);
				}
			}
			$uSession = $user->getUserSession();
			$uCost = $uSession['max_cost_count'];
			$_soldier = new Soldier();
			$uSoldiers = $_soldier->sortSoldier( $uSoldiers, 9, true );//士官排序部分(Test DATA)
			foreach ($uSoldiers as $soldier) {
				if( ++$i > 3 || $amryCost += $soldier['cost'] > $uCost ) break;//数量达到上限或成本值超过成本上限
				$new[] = $soldier['id'];
			}
			if( $new !== $old) {
				for ($i=0;$i<3;$i++) {
					if( !isset($new[$i]) ) break;
					$sort = $i + 1;
					$this->_udb->set('army_id', $id);
					$this->_udb->set('sort', $sort);
					$this->_udb->dataUpdate('user_soldiers', "id={$new[$i]}");
				}
			}
			if( $oldIds = array_diff($old, $new) ) {
				$_soldier->updateSoldier( array('army_id'=>0,'sort'=>0), "'id' in (".implode(',',$newIds).")");
			}
		}
		return true;
	}
	
	/**
	 * 提高部队优先度
	 * @param $id 部队ID
	 */
	public function setPriorityArmy( $id )
	{
		if( $id > 1 ) {
			$old_id = $id - 1;
			$tmp_id = 99;
			$this->_udb->set('army_id', $tmp_id);
			$this->_udb->dataUpdate('user_soldiers', "uuid=$this->uuid AND army_id=$old_id");
			$this->_udb->set('army_id', 'army_id-1', false);
			$this->_udb->dataUpdate('user_soldiers', "uuid=$this->uuid AND army_id=$id");
			$this->_udb->set('army_id', $id);
			$this->_udb->dataUpdate('user_soldiers', "uuid=$this->uuid AND army_id=$tmp_id");
		}
	}
	
	/**
	 * 部队实际攻防算法
	 * @param $army	我方部队资源
	 * @param $f_army	敌方部队资源
	 * @return $newArmy
	 * 		   -reality_attack		实力攻击力(我方)
	 * 		   -reality_defense		实际防御力(敌方)
	 * 会影响到攻防的因素有：国籍、武器
	 */
	public function filterArmyData( &$army, &$f_army )
	{
		$_skill = new Skill();
		$uSkills = $_skill->skillsTrigger( $army['member'] );
		$f_uSkills = $_skill->skillsTrigger( $f_army['member'], 1 );
		//我方攻击增强
		if( isset($uSkills['1&1']) ) {
			$_skill->skillEffect($army['member'], $uSkills['1&1']);
		}
		//敌方防御增强
		if( isset($f_uSkills['1&2']) ) {
			$_skill->skillEffect($f_army['member'], $f_uSkills['1&2']);
		}
		//敌方防御削弱
		if( isset($uSkills['2&2']) ) {
			$_skill->skillEffect($f_army['member'], $uSkills['2&2']);
		}
		//我方攻击削弱
		if( isset($f_uSkills['2&1']) ) {
			$_skill->skillEffect($army['member'], $uSkills['2&1']);
		}
		$_skill->resetArmy( $army );
		$_skill->resetArmy( $f_army );
	}
}
?>