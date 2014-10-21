<?php
/**
* @description	基地首页controllers
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/27
* @modifyTime
				2012/03/27	文件创建	朱勇
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
	/*
	* 基地首页
	*/
	case 'basecamp':
		require_once (MODEL_SRC_ROOT.'User.php');
		$user_module_self = new User($uuid);
		$user_armies = $user_module_self->getUserArmies(1);
		$user_session = $user_module_self->getUserSession();

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

		$friend = array();
		
		require_once (MODEL_SRC_ROOT.'Message.php');

		$user_session_self = $user_module_self->getUserSession();

		$message_module = new Message($uuid);

		$all_hello = $message_module->getAllHello();
		
		if($all_hello && is_array($all_hello))
		{
			foreach($all_hello as &$all_hello_value)
			{
				$user_module_friend = new User($all_hello_value['fid']);
				$user_session_friend = $user_module_friend->getUserSession();

				$all_hello_value['adddate'] = date('Y-m-d H:i', $all_hello_value['addtime']);
				$all_hello_value['nickname'] = $user_session_friend['nickname'];
				$all_hello_value['userid'] = $user_session_friend['uuid'];
				unset($user_module_friend);
				unset($user_session_friend);
			}
			unset($all_hello_value);
		}

		$friend_all = array_chunk($all_hello, 3);
		$friend_list = $friend_all[0];
		
		$tplVars['friend'] = $friend_list;
		$tplVars['user_armies'] = $user_armies;
		$tplVars['user_session'] = $user_session;
		$tplVars['event_all'] = $event_all;

		$tplname = 'basecamp/basecamp.html';

		break;

	default:
}

tpl::show($tplname);

/* End of file Basecamp.php */
/* Location: /controllers/Basecamp.php */