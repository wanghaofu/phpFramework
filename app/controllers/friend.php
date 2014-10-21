<?php
/**
* @description	友军controllers
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/26
* @modifyTime
				2012/03/26	文件创建	朱勇
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
	* 友军申请页面
	*/
	case 'friend_view':
		$friend_id = trim($IN['friend_id']);

		$tplVars['friend_id'] = $friend_id;
	
		$tplname = 'friend/friend_view.html';

		break;

	/*
	* 友军申请功能
	*/
	case 'friend_action':
		$friend_id = trim($IN['friend_id']);
		$content = trim($IN['content']);

		require_once (MODEL_SRC_ROOT.'Friend.php');
		$friend_module_self = new Friend($uuid);
		$friend_module_friend = new Friend($friend_id);

		$param_send = array(
						'uuid' => $uuid,
						'friend_id' => $friend_id,
						'content' => $content,
						);
		$friend_module_self->Send($param_send);

		$param_receive = array(
						'uuid' => $friend_id,
						'friend_id' => $uuid,
						'content' => $content,
						);
		$friend_module_friend->Receive($param_receive);

		$tplname = 'friend/friend_action.html';

		break;

	/*
	* 申请中的友军
	*/
	case 'friend_ing':
		$page = empty($IN['page']) ? 1 : trim($IN['page']);

		require_once (MODEL_SRC_ROOT.'Friend.php');
		$friend_module_self = new Friend($uuid);

		$friend_ing = array();
		$friend_now = array();

		$all_receive = $friend_module_self->getAllReceive();
		
		if($all_receive && is_array($all_receive))
		{
			require_once (MODEL_SRC_ROOT.'User.php');
			
			foreach($all_receive as $all_receive_value)
			{
				if($all_receive_value['status'] == 0)
				{
					$user_module = new User($all_receive_value['friend_id']);
					$user_session = $user_module->getUserSession();

					$friend_ing[] = $user_session;
					unset($user_module);
				}
				else
				{
					$friend_now[] = $all_receive_value['friend_id'];
				}
			}
			unset($all_receive_value);
		}

		$friend_ing_all = array_chunk($friend_ing, 10);

		$page_all = count($friend_ing_all);

		$friend_ing_list = $friend_ing_all[$page - 1];

		$prev = $page - 1 < 1 ? 1 : $page - 1;
		$next = $page + 1 > $page_all ? $page_all : $page + 1;
		
		$tplVars['friend_ing'] = $friend_ing_list;
		$tplVars['num_ing'] = count($friend_ing);
		$tplVars['num_now'] = count($friend_now);
		$tplVars['page'] = $page;
		$tplVars['page_all'] = $page_all;
		$tplVars['prev'] = $prev;
		$tplVars['next'] = $next;

		$tplname = 'friend/friend_ing.html';

		break;

	/*
	* 现在的友军
	*/
	case 'friend_now':
		$page = empty($IN['page']) ? 1 : trim($IN['page']);
		
		require_once (MODEL_SRC_ROOT.'User.php');
		require_once (MODEL_SRC_ROOT.'Friend.php');

		$user_module_self = new User($uuid);
		$user_session_self = $user_module_self->getUserSession();

		$friend_module_self = new Friend($uuid);

		$friend_ing = array();
		$friend_now = array();

		$all_receive = $friend_module_self->getAllReceive();
		
		if($all_receive && is_array($all_receive))
		{
			foreach($all_receive as $all_receive_value)
			{
				if($all_receive_value['status'] == 1)
				{
					$user_module_friend = new User($all_receive_value['friend_id']);
					$user_session_friend = $user_module_friend->getUserSession();

					$friend_now[] = $user_session_friend;
					unset($user_module_friend);
					unset($user_session_friend);
				}
				else
				{
					$friend_ing[] = $all_receive_value['friend_id'];
				}
			}
			unset($all_receive_value);
		}

		$friend_now_all = array_chunk($friend_now, 10);
		$page_all = count($friend_now_all);

		$friend_now_list = $friend_now_all[$page - 1];

		$prev = $page - 1 < 1 ? 1 : $page - 1;
		$next = $page + 1 > $page_all ? $page_all : $page + 1;
		
		$tplVars['friend_now'] = $friend_now_list;
		$tplVars['num_ing'] = count($friend_ing);
		$tplVars['num_now'] = count($friend_now);
		$tplVars['num_max'] = $user_session_self['max_friend_count'];
		$tplVars['page'] = $page;
		$tplVars['page_all'] = $page_all;
		$tplVars['prev'] = $prev;
		$tplVars['next'] = $next;

		$tplname = 'friend/friend_now.html';

		break;

	case 'manual_search':
		require_once (MODEL_SRC_ROOT.'User.php');

		$user_module_self = new User($uuid);
		$user_session_self = $user_module_self->getUserSession();
	
		break;

	default:
}

tpl::show($tplname);

/* End of file Friend.php */
/* Location: /controllers/Friend.php */