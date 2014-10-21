<?php
/**
* @description	GM工具模块controllers
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/09
* @modifyTime
				2012/03/09	文件创建	朱勇
*/

require_once ('Login.php'); // 登录验证

$type = trim($IN['type']);
$module_id_in = trim($IN['module_id']);

require_once ('./gm/models/Module.php');
$module_models = new Module();

switch ($type)
{
	case 'addView':
		$module_all = $module_models->getModuleAll();

		$tplVars['module_all'] = $module_all;
		
		$tplname = 'gm/module_add.html';

		break;

	case 'addAction':
		$module_name_in = trim($IN['module_name']);
		$module_url_in = trim($IN['module_url']);
		$module_grade_in = trim($IN['module_grade']);
		$parent_id_in = trim($IN['parent_id']);	

		$module_models->setModuleName($module_name_in);
		$module_models->setModuleUrl($module_url_in);
		$module_models->setModuleGrade($module_grade_in);
		$module_models->setParentId($parent_id_in);
		$module_models->setModuleStatus(0);

		$module_models->addModule();

		echo "<script language='javascript'>";
		echo "window.location.href='Module.php?num=".rand()."'";
		echo '</script>';

		break;
		
	case 'editView':
		$module_by_id = $module_models->getModuleById($module_id_in);

		if($module_by_id)
		{
			$module_id = $module_models->getModuleId();
			$module_name = $module_models->getModuleName();
			$module_url = $module_models->getModuleUrl();
			$module_grade = $module_models->getModuleGrade();
			$parent_id = $module_models->getParentId();
			
			$module_all = $module_models->getModuleAll();
		}

		$tplVars['module_id'] = $module_id;
		$tplVars['module_name'] = $module_name;
		$tplVars['module_url'] = $module_url;
		$tplVars['module_grade'] = $module_grade;
		$tplVars['parent_id'] = $parent_id;
		$tplVars['module_all'] = $module_all;
		
		$tplname = 'gm/module_edit.html';

		break;

	case 'editAction':
		$module_name_in = trim($IN['module_name']);
		$module_url_in = trim($IN['module_url']);
		$module_grade_in = trim($IN['module_grade']);
		$parent_id_in = trim($IN['parent_id']);	
	
		$module_by_id = $module_models->getModuleById($module_id_in);

		if($module_by_id)
		{
			$module_models->setModuleName($module_name_in);
			$module_models->setModuleUrl($module_url_in);
			$module_models->setModuleGrade($module_grade_in);
			$module_models->setParentId($parent_id_in);

			$module_models->setModule();
		}

		echo "<script language='javascript'>";
		echo "window.location.href='Module.php?num=".rand()."'";
		echo '</script>';

		break;

	case 'del':
		$module_by_id = $module_models->getModuleById($module_id_in);

		if($module_by_id)
		{
			$module_models->setModuleStatus(1);

			$module_models->setModule();

			$module_by_parent_id = $module_models->getModuleByParentId($module_id_in);
			
			if($module_by_parent_id && is_array($module_by_parent_id))
			{
				foreach($module_by_parent_id as $module_by_parent_id_value)
				{
					$module_by_id_value = $module_models->getModuleById($module_by_parent_id_value['module_id']);

					if($module_by_id_value)
					{
						$module_models->setModuleStatus(1);
						$module_models->setModule();
					}

					unset($module_by_id_value);
				}
				unset($module_by_parent_id_value);
			}
		}

		echo "<script language='javascript'>";
		echo "window.location.href='Module.php?num=".rand()."'";
		echo '</script>';

		break;
   
	default:
		$module_all = $module_models->getModuleAll();

		if($module_all && is_array($module_all))
		{
			$module_list = array();
			
			foreach($module_all as $module_all_value)
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
			unset($module_all_value);
		}

		$tplVars['module_list'] = $module_list;
		
		$tplname = 'gm/module.html';
}

tpl::show($tplname);

/* End of file Module.php */
/* Location: /gm/controllers/Module.php */