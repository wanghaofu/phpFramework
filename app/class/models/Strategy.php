<?php
/**
 * @name 大战略模块
 * @access public
 * $author chenliang
 * $date 2012-02
 */

class Strategy extends Init 
{
	public function __construct( $uuid = 0 )
	{
		parent::__construct( $uuid );
	}
	
	/**
	 * 统计三方的势力比算法
	 */
	public function getStraForceRate()
	{
		//Test Data
		$forceRate = array(
			'ARC'	=>	10,
			'ICE'	=>	30,
			'FOX'	=>	60
		);
		return $forceRate;
	}
	
	/**
	 * 获取用户出征大战略的部队信息
	 * @param $stra_serial_num 副本ID
	 */
	public function getStra( $stra_serial_num )
	{
		//获取地图信息
		$starFlag = false;
		$insertFlag = false;
		$stra = $this->getStraTeam( $stra_serial_num );
		$pStras = Prop::init('strategy')->getAll();
		if( !$map_id = $stra['map_id'] ) {//还未进入副本
			$insertFlag = true;
			$map_id = array_rand($pStras);
			$pStra = $pStras[ $map_id ];//随机一张地图
			$_stra_team = new StraTeam();
			$_stra_team->teamUpdate( 'map_id', $map_id, "id=$stra_serial_num");
		}
		$star = array(1=>0,2=>0,3=>0,4=>0,5=>0,6=>0);
		$starFlag = false;
		$pStra = $pStras[ $map_id ];
		if( $pStra['star'] ) {
			$starArr = explode(',', $pStra['star']);
			foreach ($starArr as $st) {
				$star[ $st ] = 1;
			}
			$starFlag = true;
		}
		$uStra = $pStra;
		$uStra['stra_id'] = $stra_serial_num;
		$uStra['stra_time'] = 600000 - (time() - $stra['ctime']) * 1000;
		//获取6个玩家的信息
		$sql = "SELECT * FROM stra_session WHERE stra_serial_num=$stra_serial_num ORDER BY uuid";
		if($res = $this->_stra_udb->getRows($sql)) {
			foreach ($res as $rs) {
				${"star_".$rs['uuid']} = 0;//有利星级数
				${"leader_".$rs['uuid']} = '';//用户LeaderIcon
				$uStra[ 'force_'.$rs['force'] ][ $rs['uuid'] ] = $rs;
				$uStra[ 'force_'.$rs['force'] ][ $rs['uuid'] ]['star'] = &${"star_".$rs['uuid']};
				$uStra[ 'force_'.$rs['force'] ][ $rs['uuid'] ]['leader'] = &${"leader_".$rs['uuid']};
				$uuids[] = $rs['uuid'];
			}
		}
		//获取6个玩家的队长信息
		$sql = "SELECT * FROM stra_soldiers WHERE uuid in (".implode(',', $uuids).") ORDER BY uuid";
		if($res = $this->_stra_udb->getRows($sql)) {
			$pSoldiers = Prop::init('soldier')->getAll();
			$pWeapons = Prop::init('weapon')->getAll();
			foreach ($res as $rs) {
				$pSoldier = $pSoldiers[ $rs['soldier_id'] ];
				if($rs['sort'] == 1) {//队长
					${"leader_".$rs['uuid']} = $pSoldier['gf'];
				}
				if(!$starFlag) continue;
				if( in_array($pSoldier['weapon_id'], explode(',', $pStra['star'])) ) {//有利匹配成功
					${"star_".$rs['uuid']}++;//有利星级数增加
				}
			}
		}
		if($starFlag && $insertFlag) {
			foreach ($uuids as $uuid) {
				if(${"star_".$uuid} > 0) {
					$this->_stra_udb->set('star_count', ${"star_".$uuid});
					$this->_stra_udb->dataUpdate('stra_session', "uuid=$uuid");
				}
			}
		}
		return $uStra;
	}
	
	/**
	 * 获取参加大战略的士官
	 */
	public function getStraSoldiers()
	{
		return $this->_stra_uCache->getData('straSoldiers');
	}
	
	/**
	 * 获取当前大战略基本信息
	 */
	public function getStraSession()
	{
		$stra_session = $this->_stra_uCache->getData('straSession');
		return $stra_session;
	}
	
	/**
	 * 获取当前任务进度
	 * @param $parent_mission_id 当前副本系列号
	 */
	public function getStraMissions( $stra_serial_num )
	{
		$straMissions = $this->_stra_uCache->getData('straMissions');
		if( !$straMissions ) {
			$_stra_mission = new StraMission();
			$_stra_mission->newMission( $stra_serial_num );
			$straMissions = $this->_stra_uCache->getData('straMissions');
		}
		return $straMissions;
	}
	
	/**
	 * 获取大战略中某个任务的信息
	 * @param $mission_id 任务ID
	 */
	public function getStraMission( $mission_id )
	{
		$mission = $this->_stra_uCache->getData('straMissions');
		return $mission[ $mission_id ];
	}
	
	/**
	 * 获取大战略副本信息
	 */
	public function getStraTeam( $id )
	{
		$teams = $this->_stra_uCache->getData('straTeams');
		return $teams[ $id ];
	}
	
	/**
	 * 申请参加大战略
	 * @param $army_id 出击部队ID
	 */
	public function apply( $army_id )
	{
		if( !$straSession = $this->getStraSession() ) {
			global $user;
			if($uSession = $user->getUserSession()) {
				if($uArmy = $user->getUserArmies( $army_id )) {
					$this->_stra_udb->addData('uuid', $this->uuid);
					$this->_stra_udb->addData('force', $uSession['force']);
					$this->_stra_udb->addData('level', $uSession['level']);
					$this->_stra_udb->addData('coin', $uSession['coin']);
					$this->_stra_udb->addData('energy', $uSession['energy']);
					$this->_stra_udb->addData('ctime', time());
					$this->_stra_udb->dataInsert('stra_session');
					
					foreach ($uArmy['member'] as $soldier) {
						$this->_stra_udb->addData('uuid', $this->uuid);
						$this->_stra_udb->addData('soldier_id', $soldier['soldier_id']);
						$this->_stra_udb->addData('army_id', $army_id);
						$this->_stra_udb->addData('sort', $soldier['sort']);
						$this->_stra_udb->addData('level', $soldier['level']);
						$this->_stra_udb->addData('attack', $soldier['attack']);
						$this->_stra_udb->addData('defense', $soldier['defense']);
						$this->_stra_udb->addData('weapon_level', $soldier['weapon_level']);
						$this->_stra_udb->addData('weapon_count', $soldier['weapon_count']);
						$this->_stra_udb->addData('weapon_count_limit', $soldier['weapon_count_limit']);
						$this->_stra_udb->addData('skill_level', $soldier['skill_level']);
						$this->_stra_udb->dataInsert('stra_soldiers');
					}
				}
			}
		}
	}
	
	/**
	 * 配对,寻找其他同伴与敌方人员
	 * @return $array array 六个人的信息
	 */
	public function matching( $isLast = false )
	{
		//获取用户当前配对情况
		$straSession = $this->getStraSession();
		$uuids = array( $this->uuid );
		if( $straSession['force_serial_num'] != 0 && $straSession['stra_serial_num'] != 0  ) {//已经配对成功
			$stra_serial_num = $straSession['stra_serial_num'];
		} else {
			$_stra_match = new StraMatch( $straSession );
			if( $straSession['force_serial_num'] == 0 && $straSession['stra_serial_num'] == 0 ) {
				if( !$_stra_match->forceMatch($isLast) ) return false;
			}
			if( !$_stra_match->straMatch($isLast) ) return false;
			$stra_serial_num = $_stra_match->session['stra_serial_num'];
		}
		return $stra_serial_num;
	}
	
	/**
	 * 大战略进军行为
	 *
	 * @param $mission_id 任务ID
	 * @param $schedule 进度
	 */
	public function doMission( $mission_id )
	{
		$pvpFlag = 0;
		$field = 'schedule';
		$enemyFlag = false;
		$mission = $this->getStraMission( $mission_id );
		if( $mission['enemy_usermark'] == $this->uuid ) {
			$field = 'enemy_schedule';
			$enemyFlag = true;
		}
		$rand = rand(1,5) * 10;
		$schedule = $mission[ $field ] + $rand;
		$schedule = $schedule<100 ? $schedule : 100;
		$_stra_mission = new StraMission();
		$_stra_mission->updateMission($mission_id, $field, $schedule);
		if( $schedule == 100) {//进军完成
			if(!$enemyFlag) {//首次占领
				$_stra_mission->updateMission($mission_id, 'host', $this->uuid);
			} else {		 //以侵略方身份
				$pvpFlag = 1;
			}
		}
		return $pvpFlag;
	}
	
	/**
	 * 移动至新的任务区域
	 * @param $mission_id 新区域的任务ID
	 */
	public function move( $mission_id )
	{
		$pvpFlag = 0;
		$mission = $this->getStraMission( $mission_id );
		if( $mission['host'] == 0 || $mission['host'] == $this->uuid) {//无人区域 或 自己占领区域
			$this->_stra_udb->set('usermark', 0);
			$this->_stra_udb->dataUpdate('stra_missions', "usermark=$this->uuid");
			$_stra_mission = new StraMission();
			$_stra_mission->updateMission( $mission_id, 'usermark', $this->uuid );
		} else {//敌方区域
			if($mission['schedule'] < 100) {//敌方未占领
				$this->_stra_udb->set('usermark', 0);
				$this->_stra_udb->dataUpdate('stra_missions', "usermark=$this->uuid");
				/* 此处作为侵略方计入 */
				$_stra_mission = new StraMission();
				$_stra_mission->updateMission( $mission_id, 'enemy_usermark', $this->uuid );
			} else {//敌方已占领
				if($mission['landmine'] == 1) {//敌方布置有地雷
					//受到袭击逻辑...
					if('被炸死') {
						$this->_stra_udb->set('usermark', 0);
						$this->_stra_udb->dataUpdate('stra_missions', "usermark=$this->uuid");
						return -1;
					}
				}
				//跳转至PVP
				$pvpFlag = 1;
			}
		}
		return $pvpFlag;
	}
	
	/**
	 * 设定地雷
	 * @param $mission_id
	 */
	public function landmineSet( $mission_id )
	{
		$_stra_mission = new StraMission();
		$_stra_mission->updateMission( $mission_id, 'landmine', 1 );
	}
}
?>