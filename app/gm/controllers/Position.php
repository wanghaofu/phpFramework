<?php
/**
* @description	GM工具职位模块controllers
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/09
* @modifyTime
				2012/03/09	文件创建	朱勇
*/

require_once ('Login.php'); // 登录验证

$type = trim($IN['type']);
$position_id_in = trim($IN['position_id']);

require_once ('./gm/models/position.php');
$position_models = new Position();

require_once ('./gm/models/Module.php');
$module_models = new Module();

switch ($type)
{
	case 'addView':
		$module_all = $module_models->getModuleAll();

		$tplVars['module_all'] = $module_all;
		
		$tplname = 'gm/position_add.html';

		break;

	case 'addAction':
		$position_name_in = trim($IN['position_name']);
		$module_list_arr = $IN['module_list_arr'];
		if($module_list_arr && is_array($module_list_arr))
		{
			$module_list = implode(',', $module_list_arr);
		}
	
		$position_models->setPositionName($position_name_in);
		$position_models->setModuleList($module_list);
		$position_models->setPositionStatus(0);

		$position_models->addPosition();

		echo "<script language='javascript'>";
		echo "window.location.href='Position.php?num=".rand()."'";
		echo '</script>';

		break;
		
	case 'editView':
		$position_by_id = $position_models->getPositionById($position_id_in);

		if($position_by_id)
		{
			$position_id = $position_models->getPositionId();
			$position_name = $position_models->getPositionName();
			$module_list = $position_models->getModuleList();
			if($module_list)
			{
				$module_list_arr = explode(',', $module_list);
			}
		}

		$module_all = $module_models->getModuleAll();
		if($module_all && is_array($module_all))
		{
			foreach($module_all as &$module_all_value)
			{
				if( $module_list_arr && is_array($module_list_arr) && in_array($module_all_value['module_id'], $module_list_arr) )
				{
					$module_all_value['check'] = 1;
				}
				else
				{
					$module_all_value['check'] = 0;
				}
			}
			unset($module_all_value);
		}

		$tplVars['module_all'] = $module_all;
		$tplVars['position_id'] = $position_id;
		$tplVars['position_name'] = $position_name;
		$tplVars['module_list'] = $module_list;
		$tplVars['module_list_arr'] = $module_list_arr;
		
		$tplname = 'gm/position_edit.html';

		break;

	case 'editAction':
		$position_name_in = trim($IN['position_name']);
		$module_list_arr = $IN['module_list_arr'];
		if($module_list_arr && is_array($module_list_arr))
		{
			$module_list = implode(',', $module_list_arr);
		}
	
		$position_by_id = $position_models->getPositionById($position_id_in);

		if($position_by_id)
		{
			$position_models->setPositionName($position_name_in);
			$position_models->setModuleList($module_list);

			$position_models->setPosition();
		}

		echo "<script language='javascript'>";
		echo "window.location.href='Position.php?num=".rand()."'";
		echo '</script>';

		break;

	case 'del':
		$position_by_id = $position_models->getPositionById($position_id_in);

		if($position_by_id)
		{
			$position_models->setPositionStatus(1);

			$position_models->setPosition();
		}

		echo "<script language='javascript'>";
		echo "window.location.href='Position.php?num=".rand()."'";
		echo '</script>';

		break;
   
	default:
		$position_all = $position_models->getPositionAll();

		$tplVars['position_all'] = $position_all;
		
		$tplname = 'gm/position.html';
}

tpl::show($tplname);

/* End of file Position.php */
/* Location: /gm/controllers/Position.php */