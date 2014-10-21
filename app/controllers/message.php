<?php
/**
* @description	互动消息controllers
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
	* 礼炮一览
	*/
	case 'hello_all':
		$page = empty($IN['page']) ? 1 : trim($IN['page']);

		$friend = array();
		
		require_once (MODEL_SRC_ROOT.'User.php');
		require_once (MODEL_SRC_ROOT.'Message.php');

		$user_module_self = new User($uuid);
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

		$friend_all = array_chunk($all_hello, 10);
		$page_all = count($friend_all);

		$friend_list = $friend_all[$page - 1];

		$prev = $page - 1 < 1 ? 1 : $page - 1;
		$next = $page + 1 > $page_all ? $page_all : $page + 1;
		
		$tplVars['friend'] = $friend_list;
		$tplVars['page'] = $page;
		$tplVars['page_all'] = $page_all;
		$tplVars['prev'] = $prev;
		$tplVars['next'] = $next;

		$tplname = 'message/hello_all.html';

		break;

	/*
	* 发送礼炮
	*/
	case 'hello':
		$fid = trim($IN['fid']);
		$content = '祝炮';

		require_once (MODEL_SRC_ROOT.'Message.php');
		$message_module = new Message($fid);

		$param = array(
						'uuid' => $fid,
						'fid' => $uuid,
						'content' => $content,
						'type' => '1',
						);
		$message_module->addMessage($param);

		require_once (MODEL_SRC_ROOT.'User.php');

		$user_module = new User($uuid);
		$user_session = $user_module->getUserSession();

		require_once (MODEL_SRC_ROOT.'Session.php');

		//$session_module_self = new Session($uuid);
		//$session_module_self->updateSession('pt', 2, TRUE);

		//$session_module_f = new Session($fid);
		//$session_module_f->updateSession('pt', 2, TRUE);

		$tplVars['nickname'] = $user_session['nickname'];
		$tplVars['pt_old'] = $user_session['pt'];
		$tplVars['pt_new'] = $user_session['pt'] + 2;
		$tplVars['fid'] = $fid;

		$tplname = 'message/hello.html';

		break;

	/*
	* 发送消息
	*/
	case 'message':
		$fid = trim($IN['fid']);
		$content = trim($IN['content']);

		require_once (MODEL_SRC_ROOT.'Message.php');
		$message_module = new Message($fid);

		$param = array(
						'uuid' => $fid,
						'fid' => $uuid,
						'content' => $content,
						'type' => '0',
						);
		$message_module->addMessage($param);

		header("Location:Message.php?act=hello_all&num=".rand());

		break;

	default:
}

tpl::show($tplname);

/* End of file Message.php */
/* Location: /controllers/Message.php */