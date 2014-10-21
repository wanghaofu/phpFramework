<?php
/**
* @description	GM工具左边栏
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/07
* @modifyTime
				2012/03/07	文件创建	朱勇
*/

require_once ('Login.php'); // 登录验证

require_once ('./gm/models/Module.php');
$module_models = new Module();

$module_all = $module_models->getModuleAll();

if($module_all && is_array($module_all))
{
	$module_list = array();
	
	foreach($module_all as $module_all_value)
	{
		if($admin_self['module_list_arr'] && is_array($admin_self['module_list_arr']) && in_array($module_all_value['module_id'], $admin_self['module_list_arr']))
		{
			if($module_all_value['module_grade'] == 1)
			{
				$module_list[$module_all_value['module_id']]['oneself'] = $module_all_value;
			}
			elseif($module_all_value['module_grade'] == 2)
			{
				$module_list[$module_all_value['parent_id']]['child'][$module_all_value['module_id']]['oneself'] = $module_all_value;
			}
		}
	}
	unset($module_all_value);
}

$tplVars['module_list'] = $module_list;

$tplname = 'gm/left.html';

tpl::show($tplname);

/* End of file Left.php */
/* Location: /gm/controllers/Left.php */