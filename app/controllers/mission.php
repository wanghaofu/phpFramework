<?php
/**
 * @name 任务操作页面
 * @author chenliang $date 2012-02
 */

/****** 改变工作路径　******/

chdir ( '..' );

/****** 载入配置文件及共用库 ******/

require_once ( './class/global.php' );
require_once ( './class/lib_init_db.php' ); // 初始化数据库
require_once ( './class/lib_init_cache.php' ); // 初始化缓存
require_once ( './class/lib_init_tpl.php' ); // 初始化模板

$uuid = 1;
$user = new User($uuid);

//$tpl = tpl::initKtpl();
////de($tpl);
$tpl = tpl::initSmarty();

switch ( $IN['act'] ){

	/**
	 * 任务作战选择画面
	 */
	case 'area':
		$area_id = $IN['id'];
		$uArea = $user->getUserArea( $area_id );
		$tplVars['uArea'] = $uArea;
		$tplname = "mission/area.html";
		break;
	
	/**
	 * 任务选择画面
	 */
	case 'battle':
		$battle_id = $IN['bid'];
		$uSession = $user->getUserSession();
		$uBattle = $user->getUserBattle( $battle_id );
		$tplVars['uSession'] = $uSession;
		$tplVars['uBattle'] = $uBattle;
		$tplname = "mission/battle.html";
		break;
	
	/**
	 * 任务执行画面(客户端主动请求)
	 */
	case 'mission':
		$battle_id = $IN['bid'];
		$mission_id = $IN['mid'];
		$pBattle = prop::init('battle')->getOne( $battle_id );
		$uSession = $user->getUserSession();
		$uMission = $user->getUserMission( $mission_id );
		$tplVars['battle_id'] = $pBattle['battle_id'];
		$tplVars['battle_name'] = $pBattle['battle_name'];
		$tplVars['uSession'] = $uSession;
		$tplVars['uMission'] = $uMission;
		$tplname = "mission/mission.html";
		break;
	
	/**
	 * 任务执行行为(客户端主动请求)
	 */
	case 'mission_do':
		$battle_id = $IN['bid'];
		$mission_id = $IN['mid'];
		$_mission = new Mission();
		$missionResult = $_mission->doMission( $mission_id );
		header("Location: mission.php?op=act::mission;bid::$battle_id;mid::$mission_id");
		exit;
}
//echo "<pre>";
//print_r($tplVars);
//echo "</pre>";
tpl::show( $tplname );
?>