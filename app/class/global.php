<?php
/**
Name: 全局初始化
Author: 王涛 ( tony  )
wannghaofu@63.com
 **/

require_once ('./etc/config.inc.php');
require_once ('./libs/sys/stra.php');
$gNoCacheControl = false;
if (defined ( 'CACHE_CONTROL' ) && !$gNoCacheControl) {
	header ( 'Cache-Control: ' . CACHE_CONTROL );
	header ( "Pragma: no-cache" );
}
if (! $_SERVER ['REMOTE_ADDR']) {
	error_reporting ( 7 );
} elseif (! DEBUG_MODE) {
	error_reporting ( 0 );
}
if (DEBUG_MODE) {
// 	Timer::start ();
}
//系统异常捕捉
function exception_error_handler($errno, $errstr, $errfile, $errline) {
	File::debug_log ( "$errno, $errstr, $errfile, $errline " );
}
set_error_handler ( "exception_error_handler", 0 );

// 异常捕捉
set_exception_handler ( '_exception_handler' );

function _exception_handler($e) {
	File::debug_log ( $e );

		//	exit ();
}

// 程序终止
function _exit($flag = null) {
	if (function_exists ( 'db_close_all' ))
		db_close_all ();
	if ($GLOBALS ['gCache']) {
		$GLOBALS ['gCache']->close ();
	}
	exit ( $flag );
}

function shutdown() {
	if(DEBUG_MODE)
	{
// 		Timer::pageEnd();
	}
	global $tplName;
	if (empty ( $tplName ))
		return;
	tpl::show ( $tplName );
}
register_shutdown_function ( 'shutdown' );
// 判断是否停服
	function _game_is_stoped() {
		global $gCache;
		
		if (! $_SERVER ['REMOTE_ADDR'])
			return false;
		
		include_once ('./class/lib_init_cache.php');
		$config = $gCache->getData ( 'config' );
		
		$nowTime = time ();
		
		if ($config ['game_start_time'])
			$gameStartTime = strtotime ( $config ['game_start_time'] );
		if ($config ['game_stop_time'])
			$gameStopTime = strtotime ( $config ['game_stop_time'] );
		
		// 是否在停服时间内
		if ($nowTime >= $gameStopTime && ($nowTime < $gameStartTime || ! $gameStartTime)) {
			$gameDebugIp = @explode ( "\n", str_replace ( "\r", '', $config ['game_debug_ip'] ) );
			
			$clientIp = _get_ip ();
			if (in_array ( $clientIp, $gameDebugIp ))
				return false;
			
			$ret = array ('message' => $config ['game_stop_message'], 'temp_url' => $config ['game_stop_temp_url'], 'stop_time' => $config ['game_stop_time'], 'start_time' => $config ['game_start_time'], 'remain_time' => $gameStartTime - $nowTime );
			
			return $ret;
		}
		
		return false;
	}
// 载入类库

require_once ('./libs/idata/db.php'); // 数据库操作类
require_once ('./libs/idata/Iquery.php'); // 查询处理类
require_once ('./libs/Date.php'); // 日期操作
require_once ('./libs/idata/cacheData.php'); // 缓存类

// 载入函数库
require_once ('./libs/functions/function.php'); // 数组处理函数库
require_once ('./libs/functions/function_page.php');
require_once ('./class/lib_session.php'); //session处理机制
require_once ('./libs/common/File.php'); // 文件操作


// 载入共用库


// 程序开始时间
$gStartTime = array_sum ( explode ( ' ', microtime () ) );

//获取传去参数
$IN = parse_incoming ();
stra::initIn($IN);

$gAppId = PASSPORT_APP_ID;

$gUrlRoot = Client::url_root (); // 根目录
$gUrlCurrent = Client::url_current (); // 当前页面
$gUrlReferer = Client::url_referer (); // 来路页面


// 读取 Session
//$gSession = lib_session::load_user_session (); //用户数据存储机制
//if( $gSession )
//{
//	global $gSession,$gUserId, $gUserName;
//	$userInfo = $gSession;
//	$gUserId = $gSession['user_id'];
$gUserId = 1;
//	$gUserName = $gSession['userName'];
//}
/*
// 调试登录，可跳过 Passport，设置 role_id 后即可登录指定人物
$gSession = array (
'username' => 'test',
'nickname' => 'test',
'role_id' => 1002,
);
*/
$straMode = 0;
global $time;
$time = time ();

// 设置字符编码
if (function_exists ( 'mb_internal_encoding' )) {
	mb_internal_encoding ( DEFAULT_CHARSET );
}

$argv = Client::get_argv ();
stra::setgUuid ( 1 );
//$gTaskName = $argv['task_name'];
?>