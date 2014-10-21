<?php
/**
 * @name 士官操作API
 * @author chenliang $date 2012-02
 * @author zhuyong<ZhuYong@ultizen.com> $date 2012-02
 * @modifyTime
		2012/02/21	士官列表	陈良
		2012/02/21	士官信息	陈良
		2012/02/21	文件创建	朱勇
		2012/02/23	士官合成、士官强化	朱勇
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
switch ( $IN['act'] ){
	/**
	 * 士官一览画面
	 */
	case 'list':
		$uSoldiers = $user->getUserSoldiers();
		$ruleKey = isset($IN['rule']) ? $IN['rule'] : 0;
		$isRev = $ruleKey < 0 ? TRUE : FALSE;
		$rule = abs($ruleKey);
		foreach ($uSoldiers as $sid => $soldier) {
			if(isset($IN['aid']) && $soldier['army_id'] == $IN['aid']) {
				unset($uSoldiers[ $sid ]);
				continue;
			}
			if(isset($IN['nat']) && $IN['nat'] != 0 && $IN['nat'] != $soldier['nation_id']) {
				unset($uSoldiers[ $sid ]);
			}
		}
		$_soldier = new Soldier();
		$uSoldiers = $_soldier->sortSoldier( $uSoldiers, $rule, $isRev );
		$tplVars['army_id'] = 0;
		$tplVars['replaceFlag'] = 0;
		$tplVars['addFlag'] = 0;
		if( isset($IN['aid']) ) {
			$tplVars['army_id'] = $IN['aid'];
			if( isset($IN['oid']) ) {
				$tplVars['replaceFlag'] = 1;
				$tplVars['oldSoldierId'] = $IN['oid'];
			} else {
				$tplVars['addFlag'] = 1;
			}
		}
		$tplVars["selectFlag0"] = '';
		for ($i=0;$i<=6;$i++) {
			$j = $i + 1;
			$tplVars["nation_color{$i}"] = '#262626';
			$tplVars["nation_link{$i}"] = "soldier.php?op=act::list;aid::{$IN['aid']};nat::{$i}";
			$tplVars["selectFlag{$j}"] = '';
			$tplVars["selectFlag_{$j}"] = '';
		}
		if($ruleKey == 0) {
			$tplVars["selectFlag0"] = 'selected="selected"';
		} else {
			if(!$isRev) {
				$tplVars["selectFlag{$rule}"] = 'selected="selected"';
			} else {
				$tplVars["selectFlag_{$rule}"] = 'selected="selected"';
			}	
		}
		
		$nat = isset($IN['nat']) ? $IN['nat'] : 0;
		$tplVars["nation"] = $nat;
		$tplVars["selectLink"] = "soldier.php?op=act::list;aid::{$IN['aid']};";
		
		/* 分页 Start */
		$i = $j = 0;
		$filter_uSoldiers = array();
		foreach ($uSoldiers as $sid => $soldier) {
			if(isset($IN['aid']) && $soldier['army_id'] == $IN['aid']) {
				unset($uSoldiers[ $sid ]);
				continue;
			}
			if(isset($IN['nat']) && $IN['nat'] != 0 && $IN['nat'] != $soldier['nation_id']) continue;
			$filter_uSoldiers[$i][$sid] = $soldier;
			if(++$j == 5) {
				$j = 0;
				$i++;
			}
		}
		unset($uSoldiers);
		//按国籍分类
		$tplVars["nation_color{$nat}"] = '#FF6600';
		
		$pageNum = isset($IN['page']) ? $IN['page'] : 1;
		$uSoldiers = $filter_uSoldiers[$pageNum-1];
		/* 分页 End */
		$tplVars['pageNum'] = $pageNum;
		$tplVars['totalPageNum'] = $i+1;
		$tplVars['prevPageLink'] = "soldier.php?op=act::list;aid::{$IN['aid']};nat::$nat;rule::$ruleKey";
		$tplVars['nextPageLink'] = "soldier.php?op=act::list;aid::{$IN['aid']};nat::$nat;rule::$ruleKey";
		if($pageNum > 1) {
			$prevPageNum = $pageNum - 1;
			$tplVars['prevPageLink'] = "soldier.php?op=act::list;aid::{$IN['aid']};nat::{$nat};rule::$ruleKey;page::{$prevPageNum}";
		}
		if($pageNum <= $i) {
			$nextPageNum = $pageNum + 1;
			$tplVars['nextPageLink'] = "soldier.php?op=act::list;aid::{$IN['aid']};nat::{$nat};rule::$ruleKey;page::{$nextPageNum}";
		}
		$tplVars['uSoldiers'] = $uSoldiers;
		$tplname = "soldier/list.html";
		break;
	
	/**
	 * 士官情报画面
	 */
	case 'show':
		$sid = $IN['sid'];
		$armyFlag = isset($IN['armyFlag']) ? $IN['armyFlag'] : 0;
		$army_id = isset($IN['aid']) ? $IN['aid'] : 0;
		$uSoldier = $user->getUserSoldiers( $sid );
		$tplVars['uSoldier'] = $uSoldier;
		$tplVars['armyFlag'] = $armyFlag;
		$tplVars['army_id'] = $army_id;
		$tplname = "soldier/show.html";
		break;
		
	/**
	 * 设置为Leader
	 */
	case 'leader':
		$aid = $IN['aid'];//部队ID
		$sid = $IN['sid'];//士官编号
		$_soldier = new Soldier();
		$_soldier->setLeaderSoldier( $sid );
		header("Location: army.php?op=act::show;id::$aid");
		break;
	
	/**
	 * 士官加入部队行为
	 */
	case 'join':
		$aid = $IN['aid'];
		$oid = $IN['oid'] ? $IN['oid'] : 0;
		$nid = $IN['nid'];
		$_soldier = new Soldier();
		$_soldier->addSoldier($nid, $aid, $oid);
		header("Location: army.php?op=act::show;id::$aid");
		break;
	
	/**
	 * 撤出士官行为
	 */
	case 'out':
		$aid = $IN['aid'];//部队ID
		$sid = $IN['sid'];//士官编号
		$_soldier = new Soldier();
		$_soldier->outSoldier( $sid );
		header("Location: army.php?op=act::show;id::$aid");
		break;
	
	/*
	* 2012/02/23	朱勇 start
	*/

		/**
		 * 士官合成基础卡选择页面
		 */
		case 'combView_main':
			$type = trim($IN['type']);
			if( $type == 'assist' )
			{
				$sid_main = trim($IN['sid_main']);

				$uSoldier = $user->getUserSoldiers( $sid_main );

				$uSoldiers = $user->getUserSoldiers();
				$ruleKey = isset($IN['rule']) ? $IN['rule'] : 0;
				$isRev = isset($IN['rev']) ? TRUE : FALSE;
				$_soldier = new Soldier();
				$uSoldiers = $_soldier->sortSoldier( $uSoldiers, $ruleKey, $isRev );

				$tplVars['uSoldier'] = $uSoldier;
				$tplVars['uSoldiers'] = $uSoldiers;

				$tplname = 'soldier/combination_main_assist.html';
			}
			else
			{
				$uSoldiers = $user->getUserSoldiers();
				$ruleKey = isset($IN['rule']) ? $IN['rule'] : 0;
				$isRev = isset($IN['rev']) ? TRUE : FALSE;
				$_soldier = new Soldier();
				$uSoldiers = $_soldier->sortSoldier( $uSoldiers, $ruleKey, $isRev );

				$tplVars['uSoldiers'] = $uSoldiers;

				$tplname = 'soldier/combination_main.html';
			}

			break;

		/**
		 * 士官合成素材卡选择页面
		 */
		case 'combView_assist':
			$type = trim($IN['type']);
			if( $type == 'main' )
			{
				$sid_assist = trim($IN['sid_assist']);

				$uSoldier = $user->getUserSoldiers( $sid_assist );

				$uSoldiers = $user->getUserSoldiers();
				$ruleKey = isset($IN['rule']) ? $IN['rule'] : 0;
				$isRev = isset($IN['rev']) ? TRUE : FALSE;
				$_soldier = new Soldier();
				$uSoldiers = $_soldier->sortSoldier( $uSoldiers, $ruleKey, $isRev );

				$tplVars['uSoldier'] = $uSoldier;
				$tplVars['uSoldiers'] = $uSoldiers;

				$tplname = 'soldier/combination_assist_main.html';
			}
			else
			{
				$uSoldiers = $user->getUserSoldiers();
				$ruleKey = isset($IN['rule']) ? $IN['rule'] : 0;
				$isRev = isset($IN['rev']) ? TRUE : FALSE;
				$_soldier = new Soldier();
				$uSoldiers = $_soldier->sortSoldier( $uSoldiers, $ruleKey, $isRev );

				$tplVars['uSoldiers'] = $uSoldiers;

				$tplname = 'soldier/combination_assist.html';
			}

			break;

		/**
		 * 士官合成基础卡选择页面
		 */
		case 'combView_batch':
			$type = trim($IN['type']);
			if( $type == 'assist' )
			{
				$sid_main = trim($IN['sid_main']);

				$uSoldier = $user->getUserSoldiers( $sid_main );

				$uSoldiers = $user->getUserSoldiers();
				if($uSoldiers && is_array($uSoldiers))
				{
					$uSoldiers_batch = array();
					foreach($uSoldiers as $uSoldiers_value)
					{
						if(($uSoldiers_value['star'] < 3) && ($uSoldiers_value['combination_count'] == 0))
						{
							$uSoldiers_batch[$uSoldiers_value['id']] = $uSoldiers_value;
						}
					}
					unset($uSoldiers_value);
				}

				$_soldier = new Soldier();
				$uSoldiers = $_soldier->sortSoldier( $uSoldiers, $ruleKey, $isRev );

				$tplVars['sid_main'] = $sid_main;
				$tplVars['uSoldier'] = $uSoldier;
				$tplVars['uSoldiers_batch'] = $uSoldiers_batch;

				$tplname = 'soldier/combination_batch_assist.html';
			}
			else
			{
				$uSoldiers = $user->getUserSoldiers();
				$ruleKey = isset($IN['rule']) ? $IN['rule'] : 0;
				$isRev = isset($IN['rev']) ? TRUE : FALSE;
				$_soldier = new Soldier();
				$uSoldiers = $_soldier->sortSoldier( $uSoldiers, $ruleKey, $isRev );

				$tplVars['uSoldiers'] = $uSoldiers;

				$tplname = 'soldier/combination_batch.html';
			}

			break;

		/**
		 * 士官合成页面
		 */
		case 'combView':
			$level_assist_arr = array();
			$uSoldier_assist_arr = array();
		
			$type = trim($IN['type']);
			$sid_main = trim($IN['sid_main']);

			$uSoldier_main = $user->getUserSoldiers( $sid_main );
			$level_main = $uSoldier_main['level'];

			if(!is_array($IN['sid_assist']))
			{
				$sid_assist = trim($IN['sid_assist']);
				$sid_assist_arr = array($sid_assist);
			}
			else
			{
				$sid_assist_arr = $IN['sid_assist'];
			}
			
			if($sid_assist_arr && is_array($sid_assist_arr))
			{
				foreach($sid_assist_arr as $sid_assist_arr_value)
				{
					$uSoldier_assist = $user->getUserSoldiers( $sid_assist_arr_value );
					$level_assist_arr[] = $uSoldier_assist['level'];
					$uSoldier_assist_arr[] = $uSoldier_assist;
				}
				unset($sid_assist_arr_value);
			}

			$_soldier = new Soldier();
			$comb_coin = $_soldier->combCoin($level_main, $level_assist_arr);

			$coin_old = 9999;
			if($coin_old > $comb_coin)
			{
				$allow = 'yes';
				$coin_new = (int)$coin_old - (int)$comb_coin;

				$tplVars['coin_old'] = $coin_old;
				$tplVars['coin_new'] = $coin_new;
			}
			else
			{
				$allow = 'no';
			}

			$tplVars['uSoldier_main'] = $uSoldier_main;
			$tplVars['uSoldier_assist_arr'] = $uSoldier_assist_arr;
			$tplVars['type'] = $type;
			$tplVars['comb_coin'] = $comb_coin;
			$tplVars['allow'] = $allow;

			$tplname = 'soldier/combination_view.html';

			break;

		/**
		 * 士官合成FLASH
		 */
		case 'combActionFlash':
			$sid_main = trim($IN['sid_main']);

			$uSoldier_main = $user->getUserSoldiers( $sid_main );
			$level_main = $uSoldier_main['level'];

			if(!is_array($IN['sid_assist']))
			{
				$sid_assist = trim($IN['sid_assist']);
				$sid_assist_arr = array($sid_assist);
			}
			else
			{
				$sid_assist_arr = $IN['sid_assist'];
			}

			if($sid_assist_arr && is_array($sid_assist_arr))
			{
				foreach($sid_assist_arr as $sid_assist_arr_value)
				{
					$uSoldier_assist = $user->getUserSoldiers( $sid_assist_arr_value );
					$level_assist_arr[$uSoldier_assist['id']] = $uSoldier_assist['level'];
				}
				unset($sid_assist_arr_value);
			}
			
			$_soldier = new Soldier();
			$comb_exp = $_soldier->combExp($level_main, $level_assist_arr);
			
			$uSoldier_main['exp_new'] = $uSoldier_main['exp'] + $comb_exp;

			$tplVars['sid_main'] = $sid_main;
			$tplVars['sid_assist_arr'] = $sid_assist_arr;
			$tplVars['uSoldier_main'] = $uSoldier_main;

			$tplname = 'soldier/combination_action_flash.html';

			break;

		/**
		 * 士官合成功能
		 */
		case 'combAction':
			$sid_main = trim($IN['sid_main']);

			$uSoldier_main = $user->getUserSoldiers( $sid_main );
			$level_main = $uSoldier_main['level'];

			if(!is_array($IN['sid_assist']))
			{
				$sid_assist = trim($IN['sid_assist']);
				$sid_assist_arr = array($sid_assist);
			}
			else
			{
				$sid_assist_arr = $IN['sid_assist'];
			}

			if($sid_assist_arr && is_array($sid_assist_arr))
			{
				foreach($sid_assist_arr as $sid_assist_arr_value)
				{
					$uSoldier_assist = $user->getUserSoldiers( $sid_assist_arr_value );
					$level_assist_arr[$uSoldier_assist['id']] = $uSoldier_assist['level'];
				}
				unset($sid_assist_arr_value);
			}
			
			$_soldier = new Soldier();
			$comb_exp = $_soldier->combExp($level_main, $level_assist_arr);
			$comb_coin = $_soldier->combCoin($level_main, $level_assist_arr);

			//预留，军资金判定

			$soldier_models = new Soldier($uuid);

			if ($soldier_models)
			{
				//预留，合成概率
				if($sid_assist_arr && is_array($sid_assist_arr))
				{
					foreach($sid_assist_arr as $sid_assist_arr_value)
					{
						//$soldier_models->delSoldier($sid_assist_arr_value);
					}
					unset($sid_assist_arr_value);
				}

				//预留，合成主卡经验公式
				
				$param = array(
								'exp' => $uSoldier_main['exp']+$comb_exp,
								'level' => $uSoldier_main['level'],
								'weapon_count' => $uSoldier_main['weapon_count'],
								'skill_level' => $uSoldier_main['skill_level'],
								);

				//预留，士官等级公式
				while ($param['exp'] > 100)
				{
					$param['level'] += 1;
					$param['weapon_count'] += 1;
					$param['skill_level'] += 1;

					$param['exp'] -= 100;
				}

				//$soldier_models->updateSoldier($param, array('id' => $sid_main,));

				//预留，军资金扣除

				$uSoldier_main['level_new'] = $param['level'];
				$uSoldier_main['weapon_count_new'] = $param['weapon_count'];
				$uSoldier_main['skill_level_new'] = $param['skill_level'];
			}

			$tplVars['uSoldier_main'] = $uSoldier_main;
			$tplVars['comb_exp'] = $comb_exp;
			
			$tplname = 'soldier/combination_action.html';
			break;

		/**
		 * 士官强化页面
		 */
		case 'streView':
			$sid = $IN['sid'];
			$uSoldier = $user->getUserSoldiers( $sid );

			if($uSoldier['weapon_level'] >= 99)
			{
				$allow = 'no';
			}
			else
			{
				$allow = 'yes';
			}

			$tplVars['uSoldier'] = $uSoldier;
			$tplVars['allow'] = $allow;
			
			$tplname = 'soldier/strengthen_view.html';
			break;

		/**
		 * 士官强化功能
		 */
		case 'streAction':
			$sid = $IN['sid'];

			//预留，改造手册判定

			$uSoldier = $user->getUserSoldiers( $sid );

			$soldier_models = new Soldier($uuid);

			if($soldier_models)
			{
				$param['attack'] = $uSoldier['attack']+1;
				$param['defense'] = $uSoldier['defense']+1;
				$param['weapon_level'] = $uSoldier['weapon_level']+1;

				//$soldier_models->updateSoldier($param, array('id' => $sid,));

				//预留，改造手册扣除
			}

			$uSoldier = $user->getUserSoldiers( $sid );
			$tplVars['uSoldier'] = $uSoldier;

			$tplname = 'soldier/strengthen_action.html';
			break;
		
		default:

	/*
	* 2012/02/23	朱勇 end
	*/
}
//echo "<pre>";
//print_r($tplVars);
//echo "</pre>";
tpl::show( $tplname );
/* End of file Soldier.php */
/* Location: /controllers/Soldier.php */