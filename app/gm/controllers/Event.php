<?php
/**
* @description	GM工具杂志模块controllers
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/22
* @modifyTime
				2012/03/22	文件创建	朱勇
*/

require_once ('Login.php'); // 登录验证

$type = trim($IN['type']);
$errormsg = trim($IN['errormsg']);
$event_id_in = trim($IN['event_id']);

$tplVars['errormsg'] = $errormsg;

require_once ('./gm/models/Event.php');
$event_models = new Event();

switch ($type)
{
	case 'addView':
		$tplname = 'gm/event_add.html';

		break;

	case 'addAction':
		$title_in = trim($IN['title']);
		$content_in = trim($IN['content']);
		
		$event_models->setTitle($title_in);
		$event_models->setContent($content_in);
		$event_models->setAddtime(time());
		$event_models->setEventStatus(0);

		$event_models->addEvent();

		echo "<script language='javascript'>";
		echo "window.location.href='Event.php?num=".rand()."'";
		echo '</script>';

		break;
		
	case 'editView':
		$event_by_id = $event_models->getEventById($event_id_in);

		if($event_by_id)
		{
			$event_id = $event_models->getEventId();
			$title = $event_models->getTitle();
			$content = $event_models->getContent();
			$addtime = $event_models->getAddtime();
		}

		$tplVars['event_id'] = $event_id;
		$tplVars['title'] = $title;
		$tplVars['content'] = $content;
		$tplVars['addtime'] = $addtime;
		
		$tplname = 'gm/event_edit.html';

		break;

	case 'editAction':
		$event_id_in = trim($IN['event_id']);
		$title_in = trim($IN['title']);
		$content_in = trim($IN['content']);
	
		$event_by_id = $event_models->getEventById($event_id_in);

		if($event_by_id)
		{
			$event_models->setTitle($title_in);
			$event_models->setContent($content_in);

			$event_models->setEvent();
		}

		echo "<script language='javascript'>";
		echo "window.location.href='Event.php?num=".rand()."'";
		echo '</script>';

		break;

	case 'del':
		$event_by_id = $event_models->getEventById($event_id_in);

		if($event_by_id)
		{
			$event_models->setEventStatus(1);

			$event_models->setEvent();
		}

		echo "<script language='javascript'>";
		echo "window.location.href='Event.php?num=".rand()."'";
		echo '</script>';

		break;

		break;
   
	default:
		$event_all = $event_models->getEventAll();

		$tplVars['event_all'] = $event_all;
		
		$tplname = 'gm/event.html';
}

tpl::show($tplname);

/* End of file Event.php */
/* Location: /gm/controllers/Event.php */