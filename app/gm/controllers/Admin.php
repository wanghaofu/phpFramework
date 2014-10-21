<?php
/**
* @description	GM工具用户模块controllers
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/09
* @modifyTime
				2012/03/09	文件创建	朱勇
*/

require_once ('Login.php'); // 登录验证

$type = trim($IN['type']);
$errormsg = trim($IN['errormsg']);
$admin_id_in = trim($IN['admin_id']);

$tplVars['errormsg'] = $errormsg;

require_once ('./gm/models/position.php');
$position_models = new Position();

require_once ('./gm/models/Admin.php');
$admin_models = new Admin();

switch ($type)
{
	case 'addView':
		$position_all = $position_models->getPositionAll();

		$tplVars['position_all'] = $position_all;
		
		$tplname = 'gm/admin_add.html';

		break;

	case 'addAction':
		$admin_name_in = trim($IN['admin_name']);
		$admin_pass_in = trim($IN['admin_pass']);
		$position_id_in = trim($IN['position_id']);
	
		$admin_by_name = $admin_models->getAdminByName($admin_name_in);
		if($admin_by_name)
		{
			echo "<script language='javascript'>";
			echo "window.location.href='Admin.php?type=addView&errormsg=name_repeat&num=".rand()."'";
			echo '</script>';

			exit;
		}

		$admin_pass_in_md5 = $admin_models->PassMd5($admin_pass_in);
		
		$admin_models->setAdminName($admin_name_in);
		$admin_models->setPositionId($position_id_in);
		$admin_models->setAdminPass($admin_pass_in_md5);
		$admin_models->setAdminStatus(0);

		$admin_models->addAdmin();

		echo "<script language='javascript'>";
		echo "window.location.href='Admin.php?num=".rand()."'";
		echo '</script>';

		break;
		
	case 'editView':
		$admin_by_id = $admin_models->getAdminById($admin_id_in);

		if($admin_by_id)
		{
			$admin_id = $admin_models->getAdminId();
			$admin_name = $admin_models->getAdminName();
			$position_id = $admin_models->getPositionId();
		}

		$position_all = $position_models->getPositionAll();

		$tplVars['position_all'] = $position_all;
		$tplVars['admin_id'] = $admin_id;
		$tplVars['admin_name'] = $admin_name;
		$tplVars['position_id'] = $position_id;
		
		$tplname = 'gm/admin_edit.html';

		break;

	case 'editAction':
		$admin_name_in = trim($IN['admin_name']);
		$admin_pass_in = trim($IN['admin_pass']);
		$position_id_in = trim($IN['position_id']);
	
		$admin_by_id = $admin_models->getAdminById($admin_id_in);

		if($admin_by_id)
		{
			$admin_name = $admin_models->getAdminName();
			if($admin_name_in != $admin_name)
			{
				$admin_by_name = $admin_models->getAdminByName($admin_name_in);
				if($admin_by_name)
				{
					echo "<script language='javascript'>";
					echo "window.location.href='Admin.php?type=editView&admin_id={$admin_id_in}&errormsg=name_repeat&num=".rand()."'";
					echo '</script>';

					exit;
				}
			}
			
			$admin_models->setAdminName($admin_name_in);
			$admin_models->setPositionId($position_id_in);

			if($admin_pass_in)
			{
				$admin_pass_in_md5 = $admin_models->PassMd5($admin_pass_in);
				$admin_models->setAdminPass($admin_pass_in_md5);
			}

			$admin_models->setAdmin();
		}

		echo "<script language='javascript'>";
		echo "window.location.href='Admin.php?num=".rand()."'";
		echo '</script>';

		break;

	case 'del':
		$admin_by_id = $admin_models->getAdminById($admin_id_in);

		if($admin_by_id)
		{
			$admin_models->setAdminStatus(1);

			$admin_models->setAdmin();
		}

		echo "<script language='javascript'>";
		echo "window.location.href='Admin.php?num=".rand()."'";
		echo '</script>';

		break;

		break;
   
	default:
		$admin_all = $admin_models->getAdminAll();

		$tplVars['admin_all'] = $admin_all;
		
		$tplname = 'gm/admin.html';
}

tpl::show($tplname);

/* End of file Admin.php */
/* Location: /gm/controllers/Admin.php */