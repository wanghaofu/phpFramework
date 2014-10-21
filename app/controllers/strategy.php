<?php
/**
 * @name 大战略页面
 * @author chenliang $date 2012-02
 */

/****** 改变工作路径　******/

chdir ( '..' );

/****** 载入配置文件及共用库 ******/

require_once ( './class/global.php' );
require_once ( './class/lib_init_db.php' ); // 初始化数据库
require_once ( './class/lib_init_cache.php' ); // 初始化缓存
require_once ( './class/lib_init_tpl.php' ); // 初始化模板

$user = new User();

$straMode = 1;

switch ( $IN['act'] ){
	
	/**
	 * 大战略首页画面
	 */
	case 'index':
		$_strategy = new Strategy();
		$forceRate = $_strategy->getStraForceRate();
		$tplVars['forceRate'] = $forceRate;
		break;
	
	/**
	 * 部队选择画面
	 */
	case 'army':
		$tplVars['strategyPageFlag'] = 1;
		$uArmies = $user->getUserArmies();
		for ($i=1;$i<=3;$i++) {
			$uArmies[$i]['army_id'] = $i;
			$uArmies[$i]['flag'] = isset($uArmies[$i]['member']) ? '1' : '0';
		}
		$tplVars['uArmy_1'] = $uArmies[1];
		$tplVars['uArmy_2'] = $uArmies[2];
		$tplVars['uArmy_3'] = $uArmies[3];
		$tplname = "army/list.html";
		break;
	
	/**
	 * 申请参加大战略
	 */
	case 'apply':
		$army_id = $IN['aid'];
		$_strategy = new Strategy();
		$matchRes = $_strategy->apply( $army_id );
		$tplVars['matchFlag'] = '正在配对中,请稍后...';
		break;
		
	/**
	 * 配对画面(包含行为)(系统系统配对尚未测试...)
	 */
	case 'matching':
		$_strategy = new Strategy();
		$stra_serial_num = $_strategy->matching();
		if( !$stra_serial_num ) {
			$tplVars['stra_serial_num'] = '匹配中...停留在原始画面...';
		} else {
			header("Location: strategy.php?op=act::ready;stra_id::$stra_serial_num");
		}
		break;
	
	/**
	 * 战斗准备画面
	 */
	case 'ready':
		$stra_serial_num = $IN['stra_id'];
		$_strategy = new Strategy();
		$uStra = $_strategy->getStra( $stra_serial_num );
		$tplVars['uStra'] = $uStra;
		break;
		
	/**
	 * 地图画面(六边形)
	 */
	case 'map_gf':
		$stra_serial_num = $IN['stra_id'];
		$_strategy = new Strategy();
		$straMissions = $_strategy->getStraMissions( $stra_serial_num );
		$tplVars['straMissions'] = $straMissions;
		break;
	
	/**
	 * 地图画面(部队)
	 */
	case 'map_army';
		break;
	
	/**
	 * 移动
	 */
	case 'move':
		$stra_serial_num = $IN['stra_id'];
		$mission_id = $IN['mission_id'];
		$mission_id = $stra_serial_num * 100 + $mission_id;
		$_strategy = new Strategy();
		$pvpFlag = $_strategy->move( $mission_id );
		$tplVars['pvpFlag'] = $pvpFlag;
		break;
	
	/**
	 * 使用道具(未开放)
	 */
	case 'props':
		$_props = new Props();
		break;
	
	/**
	 * 进军
	 */
	case 'do':
		$stra_serial_num = $IN['stra_id'];
		$mission_id = $IN['mission_id'];
		$mission_id = $stra_serial_num * 100 + $mission_id;
		$_strategy = new Strategy();
		$pvpFlag = $_strategy->doMission( $mission_id );
		$tplVars['pvpFlag'] = $pvpFlag;
		break;
	
	/**
	 * 地雷设定(Flash)
	 */
	case 'landmine':
		$stra_serial_num = $IN['stra_id'];
		$mission_id = $IN['mission_id'];
		$_strategy = new Strategy();
		$_strategy->landmineSet( $mission_id );
		break;
	
	/**
	 * PVP ready
	 */
	case 'pvp_ready':
		//获取用户军队信息
		$_strategy = new Strategy();
		$straSoldiers= $_strategy->getStraSoldiers();
		$army_id = $straSoldiers[0]['army_id'];
		$uArmy = $user->getUserArmies( $army_id );
		
		//获取敌方军队信息
		$f_uuid = $IN['fuid'];
		$f_user = new User( $f_uuid );
		$_f_strategy = new Strategy( $f_uuid );
		$f_straSoldiers= $_f_strategy->getStraSoldiers();
		$f_army_id = $f_straSoldiers[0]['army_id'];;
		$f_uArmy = $f_user->getUserArmies( $f_army_id );
		
		$tplVars['uArmy'] = $uArmy;
		$tplVars['f_uArmy'] = $f_uArmy;
		$tplVars['fid'] = $f_uuid;
		$tplname = "war/ready.html";
		break;
		
	/**
	 * PVP do
	 */
	case 'pvp_do':
		$stra_serial_num = $IN['stra_id'];
		$mission_id = $IN['mission_id'];
		$army_id = $IN['aid'];
		$f_uuid = $IN['fid'];
		$_war = new War();
		$pvpResult = $_war->pvpWar($army_id, $f_uuid);
		//占领地图,预留...
		$tplVars['pvpResult'] = $pvpResult;
		$tplname = $pvpResult==1 ? "war/win.html" : "war/lose.html";
		break;
}
echo "<pre>";
print_r($tplVars);
echo "</pre>";
//tpl::show( $tplname );
?>