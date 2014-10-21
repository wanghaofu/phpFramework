<?php

/******************************************************************

	Name: 人物相关操作函数库
	Author: 王涛
	Email: wanghaofu@163.com
	QQ: 595900598

/******************************************************************/

if ( !defined ( 'IN_SYSTEM' ) )
{
	exit ( 'Access Denied' );
}

//include_once ( './class/lib_init_index.php' ); // 初始化索引配置

class User extends Init 
{
	var $db; // 人物数据库
	var $mainDb;
	var $userInfo; // 人物基本信息
	var $dbIdx; // 分库序号
	var $tableIdx; // 分表序号

	// 初始化
	function __construct($uuId = 0)
	{
		parent::__construct( $uuId );
		
		global $straMode;
		$this->straMode = $straMode;
		
		$cacheVars = array (
			// 基本信息
			'user' => array (
				'selectFrom' => 'user' . ' WHERE uuid = ' . $uuId . ' LIMIT 1',
				'first' => true,
				'lifeTime' => -1,
			),
			//基本信息
			'userSession' => array (
				'selectFrom' => 'user_session' . ' WHERE uuid = ' . $this->uuid . ' LIMIT 1',
				'first' => true,
				'lifeTime' => -1,
			),
			//士官
			'userSoldiers' => array (
				'selectFrom' => 'user_soldiers' . ' WHERE uuid = ' . $this->uuid ,
				'keyField' => 'id',
				'first' => false,
				'lifeTime' => -1,
			),
			//作战
			'userBattles' => array (
				'selectFrom' => 'user_battles' . ' WHERE uuid = ' . $this->uuid ,
				'keyField' => 'battle_id',
				'first' => false,
				'lifeTime' => -1,
			),
			//任务
			'userMissions' => array (
				'selectFrom' => 'user_missions' . ' WHERE uuid = ' . $this->uuid ,
				'keyField' => 'mission_id',
				'first' => false,
				'lifeTime' => -1,
			),
			//道具
			'userPropses' => array (
				'selectFrom' => 'user_propses' . ' WHERE uuid = ' . $this->uuid ,
				'keyField' => 'props_id',
				'first' => false,
				'lifeTime' => -1,
			),
			//记录
			'userRecord' => array (
				'selectFrom' => 'user_record' . ' WHERE uuid = ' . $this->uuid ,
				'first' => false,
				'lifeTime' => -1,
			),
		);
		
		while ( list ( $key, $item ) = @each ( $cacheVars ) ) {
			$this->_uCache->addData ( $key, $item );	
		}
		
		if($this->straMode) {
			$cacheVars = array
			(
				//大战略讯息
				'straSession' => array (
					'selectFrom' => 'stra_session' . ' WHERE uuid = ' . $this->uuid ,
					'first' => true,
					'lifeTime' => -1,
				),
				//大战略士官
				'straSoldiers' => array (
					'selectFrom' => 'stra_soldiers' . ' WHERE uuid = ' . $this->uuid ,
					'keyField' => 'id',
					'first' => false,
					'lifeTime' => -1,
				),
				//大战略任务
				'straMissions' => array (
					'selectFrom' => 'stra_missions',
					'keyField' => 'mission_id',
					'first' => false,
					'lifeTime' => -1,
				),
				//大战略Team
				'straTeams' => array (
					'selectFrom' => 'stra_teams',
					'keyField' => 'id',
					'first' => false,
					'lifeTime' => -1,
				),
			);
	
			while ( list ( $key, $item ) = @each ( $cacheVars ) ) {
				$this->_stra_uCache->addData( $key, $item );
			}
		}
		return true;
	}

	// 刷新人物信息
	function refresh ()
	{
		$this->userInfo = null;
		if ( $this->_uCache )
		{
			$this->_uCache->clean ( 'user' );
		}
	}

	// 检查人物名称
	function checkuserName ( $name, $curuserId = 0 )
	{
		global $gCache, $lang;

		$uuId = intval ( $this->getuserIdByName ( $name ) );
		if ( !$uuId || $uuId == $curuserId )
		{
			$ret = '<OK>';
		}
		else
		{
			$ret = _make_error ( $lang->show ( 'user_name_exists', $name ) );
		}
		return $ret;
	}

	// 根据通行证用户名获取人物 ID
	function getuserIdByUser ( $username )
	{
	
	}

	// 创建通行证用户名关联人物 ID 索引
	function createuserUserIdx ( $username, $uuId = 0 )
	{
	}

	// 根据人物名称获取人物 ID
	function getuserIdByName ( $name )
	{
		
	}

	// 创建人物名称索引
	function createuserNameIdx ( $name, $uuId = 0 )
	{
		
	}
	// 判断是否在线
	function isOnline ()
	{
		global $gCache, $gDate;
		
		$settingGlobal = $gCache->getData ( 'setting_global' );

		$userStates = $this->_uCache->getData ( 'user_state' );
		if ( $gDate->value - $userStates['last_action_time'] <= $settingGlobal['online_check_time'] )
		{
			return true;
		}
		else
		{
			return false;
		}		
	}
	
	/**
	 * 获取玩家基本信息
	 */
	function getUserSession()
	{
		return $this->_uCache->getData('userSession');
	}
	
	/**
	 * 获取玩家士官
	 */
	function getUserSoldiers( $id = 0 )
	{
		if($this->straMode == 1) {	//大战略模式
			$uSoldiers = $this->_stra_uCache->getData('straSoldiers');
		} else {					//常规模式
			$uSoldiers = $this->_uCache->getData('userSoldiers');
		}
		$pSoldiers = Prop::init('soldier')->getAll();
		$pNations = Prop::init('nation')->getAll();
		$pWeapons = prop::init('weapon')->getAll();
		$pSkills = prop::init('skill')->getAll();
		foreach ($uSoldiers as $sid => &$soldier) {
			//载入士官配置信息
			$soldier = array_merge($pSoldiers[ $soldier['soldier_id'] ], $soldier);
			//载入国籍配置信息
			$soldier = array_merge($soldier, $pNations[ $soldier['nation_id'] ]);
			//载入武器配置信息
			$soldier = array_merge($soldier, $pWeapons[ $soldier['weapon_id'] ]);
			//载入特殊技能配置信息(未完成)
			if( $soldier['skill_id'] ) {
				$pSkill = $pSkills[ $soldier['skill_id'] ];
				$pSkill['skill_launch_rate'] = $pSkill['skill_launch_rate'] + $pSkill['skill_launch_rate_add'] * ($soldier['skill_level'] - 1);
				$pSkill['skill_effect_val'] = $pSkill['skill_effect_val'] + $pSkill['skill_effect_val_add'] * ($soldier['skill_level'] - 1);
				$soldier = array_merge($soldier, $pSkill);
			}
			//载入勋章配置信息,预留...
			ksort($soldier);//可屏蔽此行代码
		}
		if( $id != 0 ) {
			return $uSoldiers[ $id ];
		}
		return $uSoldiers;
	}
	
	/**
	 * 获取玩家勋章(未完成)
	 */
	function getUserMedals( $id = 0 )
	{
		if( NULL == $userSoldiers ) {
			$userSoldiers = $this->_uCache->getData('userSoldiers');
		}
		if( $userSoldiers ) {
			$userMedalsArr = array();
			foreach ($userSoldiers as $soldier) {
				if( $soldier['medal'] != '' ) {
					$userMedalsArr = array_merge($userMedalsArr, explode(',',$soldier['medal']));
				}
			}
			$userMedals = array_count_values($userMedalsArr);
		}
		if( $id != 0 ){
			return $userMedals[ $id ];
		}
		return $userMedals;
	}
	
	/**
	 * 获取玩家军队
	 */
	function getUserArmies( $id = 0 )
	{
		$uArmies = array(1=>array('army_id'=>1), 2=>array('army_id'=>2), 3=>array('army_id'=>3));
		$uSession = $this->getUserSession();
		$uCost = $uSession['max_cost_count'];
		$uSoldiers = $this->getUserSoldiers();
		if( !empty($uSoldiers) ) {
			foreach ($uSoldiers as $soldier) {
				if( $army_id = $soldier['army_id'] ) {
					$uArmies[ $army_id ][ 'uCost' ] = $uCost;
					$uArmies[ $army_id ][ 'armyCost' ] += $soldier['star'];
					$uArmies[ $army_id ][ 'attack' ] += $soldier['attack'] * $soldier['weapon_count'];
					$uArmies[ $army_id ][ 'defense' ] += $soldier['defense'] * $soldier['weapon_count_limit'];
					$uArmies[ $army_id ][ 'member' ][ $soldier['sort'] ] = $soldier;
				}
			}
			if( !empty($uArmies) ) {
				foreach ($uArmies as &$army) {
					if( isset($army['member']) ) ksort($army['member']);
				}
				ksort($uArmies);
			}
			if( $id != 0 ) return $uArmies[ $id ];
		}
		return $uArmies;
	}
	
	/**
	 * 获取玩家地图区域信息
	 * @param $id 区域ID
	 */
	function getUserArea( $id )
	{
		$area_id = $id;
		//静态部分
		$pBattle = Prop::init('area')->getOne( $area_id );
		$res = Prop::init('battle')->getAll();
		foreach ($res as $rs) {
			if($area_id == $rs['area_id']) {
				$pBattles[ $rs['battle_id'] ] = $rs;
				$pBattleSort[ $rs['battle_id'] ] = $rs['sort'];
			}
		}
		//动态部分
		asort($pBattleSort);
		$bidArr = array_keys($pBattleSort);
		$firstBattleId = $bidArr[0];
		$uBattles = $this->_uCache->getData('userBattles');
		if( !isset($uBattles[ $firstBattleId ]) ) {//首次进入作战地区
			$_battle = new Battle();
			$_battle->newBattle( $firstBattleId );
			$uBattles = $this->_uCache->getData('userBattles');
		}
		$battle_ids = array_keys( $pBattles );
		foreach ($uBattles as $bid => $battle) {
			if( in_array($bid, $battle_ids) ) {
				$pBattles[ $bid ] = array_merge($pBattles[ $bid ], $battle);
				$pBattle['son'][] = $pBattles[ $bid ];
			}
		}
		return $pBattle;
	}
	
	/**
	 * 获取玩家作战信息
	 * @param $id 作战ID
	 */
	function getUserBattle( $id )
	{
		$battle_id = $id;
		//静态部分
		$pBattle = Prop::init('battle')->getOne( $battle_id );
		$res = Prop::init('mission')->getAll();
		foreach ($res as $rs) {
			if($battle_id == $rs['battle_id']) {
				$pMissions[ $rs['mission_id'] ] = $rs;
				$pMissionSort[ $rs['mission_id'] ] = $rs['sort'];
			}
		}
		//动态部分
		asort($pMissionSort);
		$midArr = array_keys($pMissionSort);
		$firstMissionId = $midArr[0];
		$uMissions = $this->_uCache->getData('userMissions');
		if( !isset($uMissions[ $firstMissionId ]) ) {//首次作战
			$_mission = new Mission();
			$_mission->newMission( $firstMissionId );
			$uMissions = $this->_uCache->getData('userMissions');
		}
		$mission_ids = array_keys($pMissions);
		foreach ($uMissions as $tid => $mission) {
			if( in_array($tid, $mission_ids) ) {
				$pMissions[ $tid ]['schedule'] = $mission['schedule'];
				$pBattle['son'][] = $pMissions[ $tid ];
			}
		}
		return $pBattle;
	}
	
	/**
	 * 获取玩家任务进度
	 * @param $mission_id 任务ID
	 */
	function getUserMission( $id )
	{
		$mission_id = $id;
		$pMission = Prop::init('mission')->getOne( $mission_id );
		$battle_id = $pMission['battle_id'];
		$pBattle = Prop::init('battle')->getOne( $battle_id );
		$pMission = array_merge($pMission, $pBattle);
		$uMissions = $this->_uCache->getData('userMissions');
		$uMission = $uMissions[ $id ];
		$uMission = array_merge($pMission, $uMission);
		return $uMission;
	}
	
	/**
	 * 获取玩家道具信息
	 */
	function getUserPropses( $id = 0 )
	{
		$pPropses = Prop::init('props')->getAll();
		$uPropses = $this->_uCache->getData('userPropses');
		foreach ($uPropses as $pid => &$props) {
			$props = array_merge($props, $pPropses[ $pid ]);
		}
		return $uPropses;
	}
	
	/**
	 * 返回玩家战历信息
	 */
	function getUserRecord()
	{
		return $this->_uCache->getData('userRecord');
	}
}

//if ( $guserId > 0 )
//{
//	$gUser = new User($guserId);
//	
//}
?>