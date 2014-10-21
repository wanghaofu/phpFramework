<?php
/**
* @description	游戏登录controllers
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/15
* @modifyTime
				2012/03/15	文件创建	朱勇
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

switch ($IN['act'])
{
	/**
	 * 登录
	 */
	case 'login':
		$time_start = mktime(11, 30, 0, date("m"), date("d"), date("Y"));
		$time_end = mktime(13, 30, 0, date("m"), date("d"), date("Y"));

		/*
		if(($time_start <= time()) && (time() <= $time_end))
		{
			header("Location:Lotto.php?act=toppage&num=".rand());

			exit;
		}
		*/
	
		if($login == 'new')
		{
			header("Location:New_Login.php?step=1&num=".rand());

			exit;
		}
		else
		{
			//杂志
			require_once ('./gm/models/Event.php');
			$event_models = new Event();
			$event_all_cache = $event_models->getEventAllCache();

			if($event_all_cache && is_array($event_all_cache))
			{
				$event_all_all = array_chunk($event_all_cache, 3);

				$event_all = $event_all_all[0];

				if($event_all && is_array($event_all))
				{
					foreach($event_all as &$event_all_value)
					{
						$event_all_value['adddate'] = date('Y-m-d', $event_all_value['addtime']);
					}
				}
			}

			//公告
			require_once ('./gm/models/Inform.php');
			$inform_models = new Inform();
			$inform_all_cache = $inform_models->getInformAllCache();

			if($inform_all_cache && is_array($inform_all_cache))
			{
				$inform_all_all = array_chunk($inform_all_cache, 3);

				$inform_all = $inform_all_all[0];

				if($inform_all && is_array($inform_all))
				{
					foreach($inform_all as &$inform_all_value)
					{
						$inform_all_value['adddate'] = date('Y-m-d', $inform_all_value['addtime']);
					}
				}
			}

			$tplVars['event_all'] = $event_all;
			$tplVars['inform_all'] = $inform_all;
			
			$tplname = 'default/top_page.html';
		}

		break;

	/*
	* 杂志连动
	*/
	case 'event':
		$event_id_in = trim($IN['event_id']);

		require_once ('./gm/models/Event.php');
		$event_models = new Event();

		$event_by_id = $event_models->getEventById($event_id_in);

		if($event_by_id)
		{
			$event_id = $event_models->getEventId();
			$title = $event_models->getTitle();
			$content = $event_models->getContent();
			$addtime = $event_models->getAddtime();

			$adddate = date('Y-m-d', $addtime);
		}

		$tplVars['event_id'] = $event_id;
		$tplVars['title'] = $title;
		$tplVars['content'] = $content;
		$tplVars['addtime'] = $addtime;
		$tplVars['adddate'] = $adddate;
	
		$tplname = 'default/event.html';

		break;

	/*
	* 通知详情
	*/
	case 'inform':
		$inform_id_in = trim($IN['inform_id']);

		require_once ('./gm/models/Inform.php');
		$inform_models = new Inform();

		$inform_by_id = $inform_models->getInformById($inform_id_in);

		if($inform_by_id)
		{
			$inform_id = $inform_models->getInformId();
			$title = $inform_models->getTitle();
			$content = $inform_models->getContent();
			$addtime = $inform_models->getAddtime();

			$adddate = date('Y-m-d', $addtime);
		}

		$tplVars['inform_id'] = $inform_id;
		$tplVars['title'] = $title;
		$tplVars['content'] = $content;
		$tplVars['addtime'] = $addtime;
		$tplVars['adddate'] = $adddate;
	
		$tplname = 'default/inform.html';

		break;

	/*
	* 通知一览
	*/
	case 'inform_all':
		$page = empty($IN['page']) ? 1 : trim($IN['page']);
		
		require_once ('./gm/models/Inform.php');
		$inform_models = new Inform();
		$inform_all_cache = $inform_models->getInformAllCache();

		if($inform_all_cache && is_array($inform_all_cache))
		{
			$inform_all_all = array_chunk($inform_all_cache, 20);

			$inform_all = $inform_all_all[$page - 1];

			if($inform_all && is_array($inform_all))
			{
				foreach($inform_all as &$inform_all_value)
				{
					$inform_all_value['adddate'] = date('Y-m-d', $inform_all_value['addtime']);
				}
			}

			$page_all = count($inform_all_all);
		}

		$prev = $page - 1 < 1 ? 1 : $page - 1;
		$next = $page + 1 > $page_all ? $page_all : $page + 1;

		$tplVars['page'] = $page;
		$tplVars['page_all'] = $page_all;
		$tplVars['prev'] = $prev;
		$tplVars['next'] = $next;
		$tplVars['inform_all'] = $inform_all;

		$tplname = 'default/inform_all.html';

		break;

	/*
	* 战绩列表
	*/
	case 'pvp_rank':
		$tplname = 'default/pvp_rank.html';

		break;

	/*
	* 新进列表
	*/
	case 'new_user':
		$tplname = 'default/new_user.html';

		break;

	/*
	* 互动页面
	*/
	case 'interact':
		$userid = trim($IN['userid']);

		$tplVars['userid'] = $userid;
	
		$tplname = 'default/interact.html';

		break;

	default:
}

tpl::show($tplname);

/* End of file Login.php */
/* Location: /controllers/Login.php */