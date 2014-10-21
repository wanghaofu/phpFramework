<?php
/**
* @description	GM工具公告模块controllers
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/22
* @modifyTime
				2012/03/22	文件创建	朱勇
*/

require_once ('Login.php'); // 登录验证

$type = trim($IN['type']);
$errormsg = trim($IN['errormsg']);
$inform_id_in = trim($IN['inform_id']);

$tplVars['errormsg'] = $errormsg;

require_once ('./gm/models/Inform.php');
$inform_models = new Inform();

switch ($type)
{
	case 'addView':
		$tplname = 'gm/inform_add.html';

		break;

	case 'addAction':
		$title_in = trim($IN['title']);
		$content_in = trim($IN['content']);
		
		$inform_models->setTitle($title_in);
		$inform_models->setContent($content_in);
		$inform_models->setAddtime(time());
		$inform_models->setInformStatus(0);

		$inform_models->addInform();

		echo "<script language='javascript'>";
		echo "window.location.href='Inform.php?num=".rand()."'";
		echo '</script>';

		break;
		
	case 'editView':
		$inform_by_id = $inform_models->getInformById($inform_id_in);

		if($inform_by_id)
		{
			$inform_id = $inform_models->getInformId();
			$title = $inform_models->getTitle();
			$content = $inform_models->getContent();
			$addtime = $inform_models->getAddtime();
		}

		$tplVars['inform_id'] = $inform_id;
		$tplVars['title'] = $title;
		$tplVars['content'] = $content;
		$tplVars['addtime'] = $addtime;
		
		$tplname = 'gm/inform_edit.html';

		break;

	case 'editAction':
		$inform_id_in = trim($IN['inform_id']);
		$title_in = trim($IN['title']);
		$content_in = trim($IN['content']);
	
		$inform_by_id = $inform_models->getInformById($inform_id_in);

		if($inform_by_id)
		{
			$inform_models->setTitle($title_in);
			$inform_models->setContent($content_in);

			$inform_models->setInform();
		}

		echo "<script language='javascript'>";
		echo "window.location.href='Inform.php?num=".rand()."'";
		echo '</script>';

		break;

	case 'del':
		$inform_by_id = $inform_models->getInformById($inform_id_in);

		if($inform_by_id)
		{
			$inform_models->setInformStatus(1);

			$inform_models->setInform();
		}

		echo "<script language='javascript'>";
		echo "window.location.href='Inform.php?num=".rand()."'";
		echo '</script>';

		break;

		break;
   
	default:
		$inform_all = $inform_models->getInformAll();

		$tplVars['inform_all'] = $inform_all;
		
		$tplname = 'gm/inform.html';
}

tpl::show($tplname);

/* End of file Inform.php */
/* Location: /gm/controllers/Inform.php */