<?php

/***************************************************************

	Name:默认页面


/***************************************************************/
exit('thanks you please waiting...!');
// 防刷新
//require_once ( './extends/refresh_limit.php' );

// 自动清除缓存
include_once ( './clear_cache.php' );

/****** 载入配置文件及共用库 ******/

require_once ( './class/global.php' );
require_once ( './class/lib_init_db.php' ); // 初始化数据库
require_once ( './class/lib_init_cache.php' ); // 初始化缓存
require_once ( './class/lib_init_tpl.php' ); // 初始化模板

/****** 局部函数 ******/


/****** 主程序 ******/

header ( "Cache-Control: no-cache" );



if ( !$gUsername || $gameStopInfo )
{
	show ( 'login.html' );
}
else
{
}
show ( 'index.html' );
?>