<?php
/**
 * @name 任务API
 * @access public
 * $author chenliang
 * $date 2012-02
 */

class Mission extends Init 
{	
	public function __construct( $uuId = 0 )
	{
		parent::__construct( $uuId );
	}
	
	/**
	 * 执行任务(进军)
	 * @return $missionResult array 任务执行结果
	 * 任务执行结果会出现以下情况：
	 * 1.获得士官卡  2.玩家升级  3.任务完成  4.出现Boss  5.行动值不足
	 */
	public function doMission( $mission_id ) 
	{
		/* 任务执行结果定义 */
		$resultContent = array('flag'=>0, 'params'=>array());
		$properties = array('getCard','levelUp','missionComplete','boss','noEnergy');
		foreach ($properties as $p) {
			$missionResult[ $p ] = $resultContent;
		}
		/**/
		$pMission = Prop::init('mission')->getOne( $mission_id );
		$uSession = $this->_uCache->getData('userSession');
		$energy = $uSession['energy'] - $pMission['energy'];
		if( $energy < 0 ) {//行动值不够
			$missionResult['noEnergy']['flag'] = 1;
		} else {
			$uMissions = $this->_uCache->getData('userMissions');
			$uMission = $uMissions[ $mission_id ];
			//更新任务进度
			if( $uMission['schedule'] < 100 ) {
				$uMission['schedule'] += $pMission['schedule_increase'];
				$schedule = $uMission['schedule'] < 100 ? $uMission['schedule'] : 100;
				$this->updateMission($mission_id, $schedule);
				//任务首次完成
				if($schedule == 100) {
					$missionResult['missionComplete']['flag'] = 1;
					$_battle = new Battle();
					$_battle->updateBattle( $pMission['battle_id'] );
					if( empty($pMission['next_mission_id']) || !$this->newMission($pMission['next_mission_id']) ) {//激活Boss战
						$missionResult['boss']['flag'] = 1;
						$_battle->doBattle( $pMission['battle_id'], 1 );
					}
				}
			}
			//任务消耗(行动值)
			$this->_udb->set('energy', $energy);
			//任务奖励(经验值)
			$exp = $uSession['exp'] + $pMission['exp'];//预留玩家升级行为，未完成...
			if($exp > 88888888) {
				//升级
				$missionResult['levelUp']['flag'] = 1;
				$this->_udb->set('exp', $exp);
				$this->_udb->set('level', 'level+1', false);
				$this->_udb->set('next_exp', $nextExp);
			}
			//任务奖励(军资金)
			$coin = $uSession['coin'] + rand($pMission['min_coin'], $pMission['max_coin']);
			$this->_udb->set('coin', $coin);
			$this->_udb->dataUpdate('user_session', "uuid=$this->uuid");	
			//任务奖励(士官卡)
			if( $pMission['prize_card'] ) {
				$cardStrArr = explode(',', $pMission['prize_card']);
				foreach ($cardStrArr as $cardStr) {
					$arr = explode('=', $cardStr);
					$card[ $arr[0] ] = $arr[1];
				}
				if( $soldier_id = $this->_randCard( $card ) ) {
					$missionResult['getCard']['flag'] = 1;
					$_soldier = new Soldier();
					$_soldier->newSoldier( $soldier_id );
					if( false === array_search( $soldier_id, explode(',', $uMission['prize_card_record']) ) ) {//从未获得过该卡片，即更新一条记录
						$prize_card_record = array();
						if( $uMission['prize_card_record'] ) $prize_card_record = explode(',', $uMission['prize_card_record']);
						array_push($prize_card_record, $soldier_id);
						$prize_card_record = implode(',', $prize_card_record);
						$this->_udb->set('prize_card_record', $prize_card_record);
						$this->_udb->dataUpdate('user_missions', "uuid=$this->uuid");
					}
				}
			}
		}
		return $missionResult;
	}
	
	/**
	 * 激活任务
	 * @return bool
	 * true:激活成功
	 * false:激活成功同时需要激活boss战
	 */
	public function newMission( $mission_id )
	{
		$this->_udb->addData('uuid', $this->uuid);
		$this->_udb->addData('mission_id', $mission_id);
		$this->_udb->dataInsert('user_missions');
		$this->_uCache->flush('userMissions');
		$pMission = Prop::init('mission')->getOne( $mission_id );
		if( $pMission['type'] == 1 ) {//当该任务为PVP任务时，继续激活后置任务
			if( !empty($pMission['next_mission_id']) ) {
				return $this->newMission($pMission['next_mission_id']);
			} else {//已激活最后一个任务，且该任务为PVP任务
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 更新任务进度
	 */
	public function updateMission( $mission_id, $schedule )
	{
		$this->_udb->set('schedule', $schedule);
		$this->_udb->dataUpdate('user_missions', "uuid=$this->uuid AND mission_id=$mission_id");
		$this->_uCache->flush('userMissions');
	}
	
	/**
	 * 埋伏地雷(未开放)
	 */
	public function setTrap( $id, $cnt )
	{
		$sql = "UPDATE `user_missions` SET `trap`=$cnt WHERE `id`=$id";
		$this->_udb->exec($sql);
	}
	
	/**
	 * 随机奖励算法
	 * @param $array
	 */
	private function _randCard( $array )
	{
		$rt = false;
		arsort($array);
		$num = rand(1,100);
		$rate = 0;
		foreach ($array as $key => $value) {
			$rate += $value;
			if( $num < $rate ) {
				$rt = $key;
				break;
			}
		}
		return $rt;
	}
}
?>