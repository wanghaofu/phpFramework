<?php
/**
* @description	游戏首次登录controllers
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/14
* @modifyTime
				2012/03/14	文件创建	朱勇
*/

/****** 改变工作路径　******/
chdir('../');

/****** 载入配置文件及共用库 ******/
require_once ('./class/global.php');
require_once ('./class/lib_init_db.php'); // 初始化数据库
require_once ('./class/lib_init_cache.php'); // 初始化缓存
require_once ('./class/lib_init_tpl.php'); // 初始化模板

//$tpl = tpl::initKtpl();
$tpl = tpl::initSmarty();

$tplVars['ui_path'] = '../views/static';

$userid = trim($IN['userid']);

$step = (empty($IN['step'])) ? 1 : trim($IN['step']);

if ( ! $userid)
{
	$userid = 1;
}

switch ($step)
{
	/**
	 * 登录首页
	 */
	case 1:
		$tplname = 'default/new_login.html';

		break;

	case 2:
		$tplname = 'default/show_how.html';

		break;

	case 3:
		$tplname = 'default/sect_all.html';

		break;

	case 4:
		$sect_id = trim($IN['sect_id']);

		$a = array();
		$a[1] = array(
						'a' => 'A.R.C.　チームバナー',
						'b' => 'A.R.C.の紹介文',
						'c' => 'A.R.C.専用ナビゲートキャラクター',
						'd' => 'キャラクターのセリフでチームの説明をします。',
						'e' => 'A.R.C.に入る',
					);
		$a[2] = array(
						'a' => 'I.C.E.　チームバナー',
						'b' => 'I.C.E.の紹介文',
						'c' => 'I.C.E.専用ナビゲートキャラクター',
						'd' => 'キャラクターのセリフでチームの説明をします。',
						'e' => 'I.C.E.に入る',
					);
		$a[3] = array(
						'a' => 'F.O.X.　チームバナー',
						'b' => 'F.O.X.の紹介文',
						'c' => 'F.O.X.専用ナビゲートキャラクター',
						'd' => 'キャラクターのセリフでチームの説明をします。',
						'e' => 'F.O.X.に入る',
					);

		$tplVars['a'] = $a[$sect_id];
		$tplVars['sect_id'] = $sect_id;

		$tplname = 'default/sectinfo.html';

		break;

	case 5:
		$sect_id = trim($IN['sect_id']);

		$a = array();
		$a[1] = array(
						'a' => 'おめでとうございます。あなたはA.R.C.に所属しました。',
						'b' => 'A.R.C.の紹介文',
						'c' => 'A.R.C.専用ナビゲートキャラクター',
					);
		$a[2] = array(
						'a' => 'おめでとうございます。あなたはI.C.E.に所属しました',
						'b' => 'I.C.E.の紹介文',
						'c' => 'I.C.E.専用ナビゲートキャラクター',
					);
		$a[3] = array(
						'a' => 'おめでとうございます。あなたはF.O.X.に所属しました。',
						'b' => 'F.O.X.の紹介文',
						'c' => 'F.O.X.専用ナビゲートキャラクター',
					);

		$tplVars['a'] = $a[$sect_id];
		$tplVars['sect_id'] = $sect_id;

		$tplname = 'default/sect_choose.html';

		break;

	default:
}

tpl::show($tplname);

/* End of file New_Login.php */
/* Location: /controllers/New_Login.php */