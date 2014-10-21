<?php
/*** 临时载入 ***/
//配置入口文件
//@include_once ( './extends/temp.inc.php' );  //can stop file

/*** 常量定义 ***/

$SYS_CONFIG['tpl_error_display'] = true; //是否在最终页面显示报错信息 true, false
$SYS_CONFIG['error_reporting']= true;

$arrHost = explode ( '.', preg_replace ( "/:.+$/", '', $_SERVER['HTTP_HOST'] ) );
$hostParts = count ( $arrHost );

// array_shift ( $arrHost );
define('PASSPORT_APP_ID','stra_1');

define ( 'COOKIE_DOMAIN', '.' . $arrHost[$hostParts - 2] . '.' . $arrHost[$hostParts - 1] ); // Cookie 域
define ( 'DEFAULT_CHARSET', 'utf-8' ); // 语言 
const DEFAULT_DB_CHAREST = DEFAULT_CHARSET;

//out put language set
const CONVERT_ENCODING_SETTING  = DEFAULT_CHARSET; //输出 语言 若不同则会 进行 输出转换SJIS, ASCII,JIS,UTF-8,EUC-JP,SJIS
// const CONVERT_ENCODING_SETTING  = 'SJIS'; //输出 语言 若不同则会 进行 输出转换SJIS, ASCII,JIS,UTF-8,EUC-JP,SJIS
const BROWSER_LANGUAGE = DEFAULT_CHARSET; //输出 语言 若不同则会 进行 输出转换Shift_JIS". 
// const BROWSER_LANGUAGE = 'Shift_JIS'; //输出 语言 若不同则会 进行 输出转换Shift_JIS". 


// const OUT_PUT_LANGUAGE = 'utf-8'; //输出 语言 若不同则会 进行 输出转换

define ( 'IN_SYSTEM', 'true' ); // Cookie 域
define( 'DEBUG_MODE' , 'false');
define( 'LOG_PATH' , './logs/'.date('Y-m-d').'.log');

define('CACHE_CONTROL','no-cache,no-store,must-revalidate');

define('CACHE_PATH', './rundata/cache/');
define('LIBS','./libs');
define( 'KTPL_DIR' , './libs/kTemplate/');
define( 'TEMPLATES_C' , './rundata/template_c/');
define('TPL_CLASS_DIR','./libs/smarty/');
define('STATIC_BASE_URL',"http://{$_SERVER['HTTP_HOST']}/views/static");
define('BASE_URL',"http://{$_SERVER['HTTP_HOST']}/controllers");



define('TPL_PATH','./views/template/');

define('HASH_LEVEL',2);

/*** 载入数据库配置 ***/

require_once ( './etc/db/dbConfig.php' );  //database config file
require_once ( './etc/db.inc.php' );  //database config file


require_once ('./libs/idata/DbFactory.php'); // 数据库工厂
require_once ( './etc/db/Split.php' );  //database config file
require_once ( './etc/db/Idb.php' );  //database config file
require_once ( './etc/cache/Ucache.php' );  //database config file
require_once ( './etc/filepath.inc.php' );  //database config file

require_once ( './etc/memcache.inc.php' );  //memcache config file

/*** 载入上传服务器配置 ***/

//require_once ( './config/server.inc.php' );  //servers config files   
?>