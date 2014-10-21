<?php
/**
 * @name 战斗操作页面
 * @author chenliang $date 2012-02
 */

/****** 改变工作路径　******/
error_reporting(0);
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
	 * 部队出击选择画面
	 */
	case 'army':
		$tplVars['warPageFlag'] = 1;
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
	
	/********************以下为Boss战部分***********************/

	/**
	 * Boss出现画面
	 */
	case 'boss':
		$battle_id = $IN['id'];
		$pBattle = Prop::init('battle')->getOne( $battle_id );
		$soldier_id = $pBattle['soldier_id'];
		$pSoldier = Prop::init('soldier')->getOne( $soldier_id );
		$tplVars['boss'] = $pSoldier;
		$tplname = "map/list.html";
		break;
	
	/**
	 * Boss战斗准备画面(Flash行为)
	 */
	case 'boss_ready':
		$army_id = $IN['aid'];
		$battle_id = $IN['bid'];
		//获取用户资产信息
		$uInfo = $user->getUserSession();
		//获取用户道具信息
		$uPropses = $user->getUserPropses();
		//获取用户部队信息
		$uArmy = $user->getUserArmies( $army_id );
		//获取boss部队信息
		$pBattle = Prop::init('battle')->getOne( $battle_id );
		$soldier_id = $pBattle['soldier_id'];
		$pSoldier = Prop::init('soldier')->getOne( $soldier_id );
		//获取武器修理费用,预留...
		$tplVars['battle_id'] = $battle_id;
		$tplVars['uInfo'] = $uInfo;
		$tplVars['uPropses'] = $uPropses;
		$tplVars['uArmy'] = $uArmy;
		$tplVars['pSoldier'] = $pSoldier;
		echo Flash::expt($tplVars);
		exit;
		
	/**
	 * Boss战战斗结果统计(Flash行为)
	 */
	case 'boss_do':
		$_war = new War();
		$_war->bossWar( $IN );
		echo 'msg_type=1';
		exit;
	
	/********************以下为PVP战部分(未完成)***********************/
			
	/**
	 * PVP对象选择画面
	 */
	case 'rival_list':
		//预留...
		break;
		
	/**
	 * PVP战斗准备画面
	 */
	case 'pvp_ready':
		$army_id = $IN['aid'];
		$f_uuid = $IN['fid'];
		$f_army_id = 1;
		//获取用户军队信息
		$uArmy = $user->getUserArmies( $army_id );
		//获取敌方军队信息
		$f_user = new User( $f_uuid );
		$f_uArmy = $f_user->getUserArmies( $f_army_id );
		$tplVars['uArmy'] = $uArmy;
		$tplVars['f_uArmy'] = $f_uArmy;
		$tplVars['fid'] = $f_uuid;
		$tplname = "war/ready.html";
		break;

	/**
	 * PVP战斗行为
	 */
	case 'pvp_do':
		$army_id = $IN['aid'];
		$f_uuid = $IN['fid'];
		$_war = new War();
		$pvpResult = $_war->pvpWar($army_id, $f_uuid);
		$tplVars['pvpResult'] = $pvpResult;
		break;
		$tplname = $pvpResult==1 ? "war/win.html" : "war/lose.html";
		
	/**
	 * 战斗履历(未开放)
	 */
	case 'score':
		$_war = new War();
		$warScore = $_war->warRecord();
		break;
}
//echo "<pre>";
//print_r($tplVars);
//echo "</pre>";
tpl::show( $tplname );
?>