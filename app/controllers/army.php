<?php
/**
 * @name 军队操作页面
 * @author chenliang $date 2012-02
 */

/****** 改变工作路径　******/
// error_reporting(0);

chdir ( '..' );

/****** 载入配置文件及共用库 ******/

require_once ( './class/global.php' );
require_once ( './class/lib_init_db.php' ); // 初始化数据库
require_once ( './class/lib_init_cache.php' ); // 初始化缓存
require_once ( './class/lib_init_tpl.php' ); // 初始化模板

$uuid = 1;
$user = new User($uuid);
switch ( $IN['act'] )
{
	
	/**
	 * 部队一览画面
	 */
	case 'list':
		$tplVars['armyPageFlag'] = 1;
		$uArmies = $user->getUserArmies();
		for ($i=1;$i<=3;$i++) {
			$uArmies[$i]['flag'] = isset($uArmies[$i]['member']) ? '1' : '0';
		}
		$tplVars['uArmy_1'] = $uArmies[1];
		$tplVars['uArmy_2'] = $uArmies[2];
		$tplVars['uArmy_3'] = $uArmies[3];
		$tplname = "army/list.html";
		break;
		
	/**
	 * 部队编成画面
	 */
	case 'show':
		$id = $IN['id'];
		$uArmy = $user->getUserArmies( $id );
		$tplVars['uArmy'] = $uArmy;
		$tplVars['joinFlag'] = count($uArmy['member'])<3 ? 1 : 0;
		$tplname = "army/show.html";
		break;
	
	/**
	 * 自动编成行为
	 */
	case 'auto_do':
		$army_id = $IN['id'];
		$_army = new Army();
		$_army->autoArmy( $army_id );
		header("Location: army.php?op=act::list");
		break;
	
	/**
	 * 部队调整顺序行为
	 */
	case 'priority_do':
		$army_id = $IN['id'];
		$_army = new Army();
		$_army->setPriorityArmy( $army_id );
		header("Location: army.php?op=act::list");
		break;
	
}

//echo "<pre>";
//print_r($tplVars);
//echo "</pre>";
tpl::show( $tplname );
?>