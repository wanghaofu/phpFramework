<?php

/***************************************************************

	Name: 账号激活


/***************************************************************/

// 防刷新

/****** 载入配置文件及共用库 ******/

require_once ( './libs/lib_global.php' );
require_once ( './libs/lib_init_db.php' ); // 初始化数据库
require_once ( './libs/lib_init_cache.php' ); // 初始化缓存
require_once ( './libs/lib_init_tpl.php' ); // 初始化模板

/****** 局部函数 ******/

// 角色名称过滤
function role_name_filter ( $roleName )
{
	global $lang;


}

/****** 主程序 ******/

header ( "Cache-Control: no-cache" );

if ( !$gUsername )
{
	_url_redirect ( '/passport.php?act=login&referer=' . $gUrlCurrent );
}

include_once ( './class/Modulse/User.php' );

$lang->load ( 'active' );
//$gCache->getData ( 'setting_global' );
//$gCache->getData ( 'setting_role' );
//$gCache->getData ( 'formula_role' );



show ( 'active.html' );
?>