<?php
/**
 * @name 士官操作模块
 * @author chenliang $2012-02
 */

class Soldier extends Init 
{	
	//初始化
	public function __construct( $uuId = 0 )
	{
		parent::__construct( $uuId );
	}
	
	/**
	 * 新建士官
	 * @param $soldierid 士官ID
	 */
	public function newSoldier( $soldier_id )
	{
		$pSoldier = Prop::init('soldier')->getOne( $soldier_id );
		$this->_udb->addData('uuid', $this->uuid);
		$this->_udb->addData('soldier_id', $soldier_id);
		$this->_udb->addData('attack', $pSoldier['attack']);
		$this->_udb->addData('defense', $pSoldier['defense']);
		$this->_udb->addData('weapon_count', $pSoldier['weapon_count_limit']);
		$this->_udb->addData('weapon_count_limit', $pSoldier['weapon_count_limit']);
		$this->_udb->dataInsert('user_soldiers');
		$this->_uCache->flush('userSoldiers');
		return $sid;
	}
	
	/**
	 * 设置士官为Leader
	 * @param $id 士官编号
	 */
	public function setLeaderSoldier( $id )
	{
		$uSoldiers = $this->_uCache->getData('userSoldiers');
		$uSoldier = $uSoldiers[ $id ];
		if($uSoldier['sort'] > 1) {
			foreach ($uSoldiers as $sid => $soldier) {
				if($soldier['army_id'] == $uSoldier['army_id'] && $soldier['sort'] == 1) {
					$this->_udb->set('sort', $uSoldier['sort']);
					$this->_udb->dataUpdate('user_soldiers', "id=$sid");
					break;
				}
			}
			$this->_udb->set('sort', 1);
			$this->_udb->dataUpdate('user_soldiers', "id=$id");
			$this->_uCache->flush('userSoldiers');
		}
	}
	
	/**
	 * 将成员加入部队
	 * @param $id 士官编号
	 * @param $army_id 部队ID
	 * @param $oldid 老士官编号
	 */
	public function addSoldier( $id, $army_id, $oldid = 0 )
	{
		if( $oldid != 0 ) {
			$sort = $this->outSoldier( $oldid, false );
		} else {
			$res = $this->_udb->getRow("SELECT max(sort) as maxsort FROM user_soldiers WHERE uuid=$this->uuid AND army_id=$army_id");
			$sort = $res['maxsort'] + 1;
		}
		$this->_udb->set('army_id', $army_id);
		$this->_udb->set('sort', $sort);
		$this->_udb->dataUpdate('user_soldiers', "id=$id");
		$this->_uCache->flush('userSoldiers');
	}
	
	/**
	 * 将成员撤出部队
	 * @param $id 士官编号
	 * $sortFlag 排序标记 值为false时，队末的士官不会自动排序
	 * @return 撤出部队的士官的排序号
	 */
	public function outSoldier( $id, $sortFlag = true )
	{
		$uSoldiers = $this->_uCache->getData('userSoldiers');
		$uSoldier = $uSoldiers[ $id ];
		if( $sortFlag && $uSoldier['sort'] != 3 ) {
			$army_id = $uSoldier['army_id'];
			$this->_udb->set('sort', 'sort-1', false);
			$this->_udb->dataUpdate('user_soldiers', "uuid=$this->uuid AND army_id=$army_id AND sort>1");	
		}
		$this->_udb->set('army_id', 0);
		$this->_udb->set('sort', 0);
		$this->_udb->dataUpdate('user_soldiers', "id=$id");
		$this->_uCache->flush('userSoldiers');
		return $uSoldier['sort'];
	}
	
	/**
	 * 士官排序
	 * @param $uSoldiers 用户士官资源
	 * @param $isRev 是否逆向排序(由高到低)  0:否 1:是
	 */
	public function sortSoldier( $uSoldiers, $ruleKey , $isRev )
	{
		$soldierCnt = count($uSoldiers);
		$ruleCnt = 8;
		$rules = array(
			0=>array('level','star','attack','defense','cost','weapon_count','weapon_id','ctime'),//default
			1=>array('attack','weapon_count','cost','star','level','defense','weapon_id','ctime'),
			2=>array('defense','weapon_count','cost','star','level','attack','weapon_id','ctime'),
			3=>array('level','star','cost','attack','defense','weapon_count','weapon_id','ctime'),
			4=>array('cost','level','star','attack','defense','weapon_count','weapon_id','ctime'),
			5=>array('weapon_count','attack','defense','cost','star','level','weapon_id','ctime'),
			6=>array('weapon_id','attack','defense','weapon_count','cost','star','level','ctime'),
			7=>array('star','level','cost','attack','defense','weapon_count','weapon_id','ctime'),
			8=>array('attack','cost'),//自动编成用
			9=>array('nationality'),
		);
		if( !$rule = $rules[$ruleKey] ) return false;
		foreach ($uSoldiers as $sid => $soldier) {
			$i = 1;
			foreach ($rule as $prop) {
				${'arr_'.$i}[] = $soldier[ $prop ];
				if( !isset(${'desc_'.$i}) ) {
					if( $prop == 'cost' ) {
						${'desc_'.$i} = true;
					} else {
						${'desc_'.$i} = false;
					}
				}
				$i++;
			}
			$ids[] = $sid;
		}
		for ($j=$i;$j<=$ruleCnt;$j++) {
			${'arr_'.$j} = array_fill(0, $soldierCnt, 0);
			${'desc_'.$j} = false;
		}
		${'arr_'.$j} = &$ids;
		
		array_multisort(
			$arr_1,SORT_NUMERIC, ($desc_1 == $isRev)?SORT_ASC:SORT_DESC,
			$arr_2,SORT_NUMERIC, ($desc_1 == $isRev)?SORT_ASC:SORT_DESC,
			$arr_3,SORT_NUMERIC, ($desc_1 == $isRev)?SORT_ASC:SORT_DESC,
			$arr_4,SORT_NUMERIC, ($desc_1 == $isRev)?SORT_ASC:SORT_DESC,
			$arr_5,SORT_NUMERIC, ($desc_1 == $isRev)?SORT_ASC:SORT_DESC,
			$arr_6,SORT_NUMERIC, ($desc_1 == $isRev)?SORT_ASC:SORT_DESC,
			$arr_7,SORT_NUMERIC, ($desc_1 == $isRev)?SORT_ASC:SORT_DESC,
			$arr_8,SORT_NUMERIC, ($desc_1 == $isRev)?SORT_ASC:SORT_DESC,
			$arr_9
		);
		foreach ($ids as $id) {
			$newUserSoldiers[ $id ] = $uSoldiers[ $id ];
		}
		return $newUserSoldiers;
	}
	
	/**
	 * 更新士官
	 * @param $array 更新内容
	 * @param $cond 更新条件
	 */
	public function updateSoldier( $array, $where )
	{
		if( is_array($where) ) {
			foreach ($where as $prop=>$value) {
				$wh[ $prop ] = "$prop=$value";
			}
			unset($where);
			$where = implode(' AND ', $wh);
		}
		foreach ($array as $key => $value) {
			$this->_udb->set($key, $value);
		}
		if($this->straMode == 1) {		//大战略模式
			$this->_stra_udb->dataUpdate('stra_soldiers', "uuid=$this->uuid AND $where");
		} else {						//常规模式
			$this->_udb->dataUpdate('user_soldiers', "uuid=$this->uuid AND $where");
		}
		$this->_uCache->flush('userSoldiers');
	}
	
	/**
	 * 删除士官
	 * @param $id 士官编号
	 */
	public function delSoldier( $id )
	{
		$this->_udb->set('delmark', 1);
		$this->_udb->dataUpdate('user_soldiers', "id=$id");
		$this->_uCache->flush('userSoldiers');
	}

	/**
	 * 士官合成军资金计算
	 *
	 * @param int $level_main 基础士官卡等级
	 * @param array $level_assist 素材士官卡等级
	 * @return int 军资金
	 */
	public function combCoin($level_main = 0, $level_assist = array())
	{
		$comb_coin = 0;
		$comb_coin_main = 0;
		$comb_coin_assist = 0;
		
		if($level_assist && is_array($level_assist))
		{
			$num_assist = count($level_assist);

			$comb_coin_main = $level_main * 100 * $num_assist;

			foreach($level_assist as $level_assist_value)
			{
				$comb_coin_assist += ($level_assist_value - 1) * 50;
			}
			unset($level_assist_value);
		}

		$comb_coin = $comb_coin_main + $comb_coin_assist;

		return $comb_coin;
	}

	/**
	 * 士官合成经验值计算
	 *
	 * @param int $level_main 基础士官卡等级
	 * @param array $level_assist 素材士官卡等级
	 * @return int 军资金
	 */
	public function combExp($level_main = 0, $level_assist = array())
	{
		$comb_exp = 0;
		
		if($level_assist && is_array($level_assist) && $level_main)
		{
			$correction_a = 0;
			$correction_b = 0;
			$correction_c = 0;
			$correction = 0;

			foreach($level_assist as $level_assist_value)
			{
				$correction_a = $level_assist_value / $level_main * 1.7;

				if($level_main > 1)
				{
					$correction_b = 0;
				}
				else
				{
					$correction_b = $level_assist_value - 1;
				}

				$correction_c = $correction_b / $level_assist_value + $correction_b;

				$correction_a_c = ($correction_a - $correction_c) * 100;

				$correction_a_c > 10 ? $correction = $correction_a_c : $correction = 10;

				//$random = rand(90, 110);
				//$correction = $correction * $random / 100;
				$correction = (int)ceil($correction);
				
				$correction_all += $correction;
			}
			unset($level_assist_value);
		}

		return $correction_all;
	}
}
?>