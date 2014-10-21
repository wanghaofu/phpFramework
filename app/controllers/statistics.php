<?php
/**
 * @name 统计页面页面
 * @author chenliang $date 2012-02
 */

/****** 改变工作路径　******/

chdir ( '..' );

/****** 载入配置文件及共用库 ******/

require_once ( './class/global.php' );
require_once ( './class/lib_init_db.php' ); // 初始化数据库
require_once ( './class/lib_init_cache.php' ); // 初始化缓存
require_once ( './class/lib_init_tpl.php' ); // 初始化模板

//require_once ( './class/Module/UserExtra.php' );
$uuid = 1;
$user = new User($uuid);

//$tpl = tpl::initKtpl();
////de($tpl);
$tpl = tpl::initSmarty();

switch ( $_GET['act'] ){
	/**
	 * 士官统计页面
	 */
	case 'soldier':
		$_userExtra = new UserExtra( $uuid );
		$userSoldiers = $_userExtra->getUserSoldiers();
		$tplVars['userSoldiers'] = $userSoldiers;
		$tplname = "soldier/list.html";
		break;

	/**
	 * 勋章统计页面
	 */
	case 'medal':
		$_prop = new Prop();
		$propMedals = $_prop->getMedals();
		$_userExtra = new UserExtra( $uuid );
		$userMedals = $_userExtra->getUserMedals();
		$tplVars['userMedals'] = $userMedals;
		$tplname = "soldier/medal.html";
		break;
}
echo "<pre>";
print_r($tplVars);
echo "</pre>";
tpl::show( $tplname );
?>