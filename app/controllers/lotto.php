<?php
/**
* @description	摇奖乐透controllers
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/02/21
* @modifyTime
				2012/02/21	文件创建	朱勇
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

$uuid = trim($IN['uuid']);
if ( ! $uuid)
{
	$uuid = 1;
}

require_once (MODEL_SRC_ROOT.'Lotto.php');
$lotto_module = new Lotto($uuid);
$user_lotto_type = $lotto_module->getLottoType($uuid);

$time_start = mktime(11, 30, 0, date("m"), date("d"), date("Y"));
$time_end = mktime(13, 30, 0, date("m"), date("d"), date("Y"));

if($IN['act'] == 'toppage')
{
	if(($time_start <= time()) && (time() <= $time_end))
	{
		if($user_lotto_type[1])
		{
			$IN['act'] = 'common';
		}
	}
	else
	{
		$IN['act'] = 'common';
	}
}

switch ($IN['act'])
{
	/**
	 * 午休首页
	 */
	case 'toppage':
		$tplname = 'lotto/toppage.html';
		break;

	/**
	 * 午休摇奖说明页面
	 */
	case 'nooning':
	
		$tplname = 'lotto/nooning.html';
		break;

	/**
	 * 非午休摇奖说明页面
	 */
	case 'common':

		if( ! $user_lotto_type[2])
		{
			$lotto_type = 2;
		}

		$tplVars['lotto_type'] = $lotto_type;

		$tplname = 'lotto/common.html';
		break;
	
	/**
	 * 摇奖页面
	 */
	case 'lottoBefore':
		$type = trim($IN['type']);

		$tplVars['type'] = $type;
	
		$tplname = 'lotto/lotto_before.html';
		break;

	/**
	 * 摇奖功能
	 */
	case 'lottoAction':
		$type = trim($IN['type']);

		$sl = Prop::init('soldier')->getAll();

		if ($sl && is_array($sl))
		{
			$soldier_star = array();

			foreach ($sl as $value_sl)
			{
				$soldier_star[$value_sl['star']][$value_sl['soldier_id']] = $value_sl;
			}
			unset($value_sl);

			switch ($type)
			{
				case '1'://午休免费摇奖
					//预留，士官星级概率
					
					$star = 1;
					$star = 2;

					break;
				
				case '2'://每日一次免费摇奖
					//预留，士官星级概率
					
					$star = 1;
					$star = 2;

					break;

				case '3'://友军PT奖券
					//预留，士官星级概率
					
					$star = 1;
					$star = 2;
					$star = 3;

					break;

				case '4'://300点数奖券
					//预留，士官星级概率
					
					$star = 3;
					$star = 4;
					$star = 5;

					break;

				case '5'://500点数奖券
					//预留，士官星级概率
					
					$star = 5;

					break;

				case '6'://银奖券
					//预留，士官星级概率
					
					$star = 5;

					break;

				case '7'://金奖券
					//预留，士官星级概率
					
					$star = 5;

					break;

				default:
			}

			$index = array_rand($soldier_star[$star], 1);
			$soldier_index = $soldier_star[$star][$index];

			$tplVars['soldier_id'] = $soldier_index['soldier_id'];
			$tplVars['type'] = $type;

			$tplname = 'lotto/lotto_after.html';
		}
		else
		{
			tpl::show_message('士官库为空！');
		}

		$tplname = 'lotto/lotto_after.html';

		break;

	/**
	 * 获得页面
	 */
	case 'lottoGet':
		$type = $IN['type'];
		$sid = $IN['soldier_id'];

		$user = new User($uuid);
	
		//预留，奖券判定

		require_once (MODEL_SRC_ROOT.'Soldier.php');
		$soldier_module = new Soldier();

		//$new_id = $soldier_module->newSoldier($sid);

		//预留，奖券扣除

		//$uSoldier = $user->getUserSoldiers( $new_id );

		require_once (MODEL_SRC_ROOT.'UserLotto.php');
		$userlotto_module = new UserLotto($uuid);

		$param = array(
						'uuid' => $uuid,
						'type' => $type,
						'sid' => $sid,
						);
		$userlotto_module->newUserLotto($param);

		if(($time_start <= time()) && (time() <= $time_end))
		{
			if( ! $user_lotto_type[1])
			{
				$lotto_type = 1;
			}
			elseif( ! $user_lotto_type[2])
			{
				$lotto_type = 2;
			}
		}
		elseif( ! $user_lotto_type[2])
		{
			$lotto_type = 2;
		}

		$tplVars['uSoldier'] = $uSoldier;
		$tplVars['lotto_type'] = $lotto_type;
	
		$tplname = 'lotto/lotto_get.html';
		break;

	default:
}

tpl::show($tplname);

/* End of file Lotto.php */
/* Location: /controllers/Lotto.php */