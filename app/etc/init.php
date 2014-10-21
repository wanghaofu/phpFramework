<?php
$SYS_CONFIG['tpl_error_display'] = false; //是否在最终页面显示报错信息 true, false
$SYS_CONFIG['error_reporting']= 'html';
define("TPL_Error_Display", $SYS_CONFIG['tpl_error_display']);
define("Error_Display", $SYS_CONFIG['error_reporting'] );


define( "DS", "/" );
define( "ROOT_PATH", "./" );
define( "ADMIN_PATH", "./" );
define( "INCLUDE_PATH", ROOT_PATH."include".DS );
define( "PLUGIN_PATH", ROOT_PATH."plugins".DS );
define( "KTPL_DIR", INCLUDE_PATH."lib".DS."kTemplate".DS );
define( "LANG_PATH", ROOT_PATH."language".DS );
define( "SYS_PATH", ROOT_PATH );
define( "CACHE_DIR", ROOT_PATH."sysdata".DS."cache".DS );
define( "KDB_DIR", INCLUDE_PATH."lib".DS."kDB".DS );
define( "MODULES_DIR", ADMIN_PATH."modules".DS );
define( "EDITORS_DIR", ADMIN_PATH."editor".DS );
define( "ADMIN_DIR", ADMIN_PATH );
define( "SETTING_DIR", ROOT_PATH."setting".DS );
define( "CLS_PATH", ROOT_PATH."classes".DS );
define( "CLASS_PATH", ROOT_PATH."classes".DS );
define( "LIB_PATH", INCLUDE_PATH."lib".DS );
define( "IN_IWPC", true );
define( "IN_SYS", true );
define ('DEFAULT_CHARSET',true);

define ( 'IN_SYSTEM', True );
define ( 'SYSTEM_CODE', '' ); // 全局密钥，请不要随意修改
define ( 'DEBUG_MODE',0  ); // 调试模式

define ( 'USE_COOKIE', 1 );

define ( 'HASH_LEVEL', 2 ); // 缓存文件目录 HASH 分布深度
define ( 'CACHE_PATH', $_SERVER['DOCUMENT_ROOT'].'/sysdata/cache/' ); // 缓存目录

define ( 'TPL_PATH', './templates' ); // 模板路径
define ( 'KTPL_DIR','./include/lib/kTemplate/'); //模板引擎路径
define ( 'SYS_path', './'); 

define ( 'DEFAULT_TPL', 'default' ); // 默认模板文件
define ( 'TPL_TEMP_PATH', '_temp' ); // 模板临时文件目录
define ( 'LANGUAGE_PATH', 'lang' ); // 语言包目录
define ( 'DEFAULT_LANGUAGE', 'zh-cn' ); // 默认语言	

define ( 'DEFAULT_CHARSET', 'UTF-8' ); // 默认字符集
define ( 'SYSTEM_CHARSET', 'UTF-8' ); // 服务器字符编码

define ( 'WEB_SERVER_CONFIG_PATH', '../config' ); // Web 服务器配置文件所在目录

define ( 'PASSPORT_APP_ID', 101 ); // 通行证服务 ID
define ( 'PASSPORT_APP_KEY', 'HANZaFR0Aw08PV1U02RzCW114UWXa26AUiIO' ); // 通行证私钥

define ( 'CACHE_CONTROL', 'max-age=0' ); // 页面缓存控制

define ( 'LOG_PATH', '../logs' ); // 日志路径
define ( 'TASK_CONTROL_NAME', 'task_control' );	// 任务调度程序名称
define ( 'TASK_LOG_PATH', '../server/logs' ); // 任务日志路径
define ( 'TASK_PID_PATH', '../server/pids' ); // 任务PID路径


define('RESOURCE_PATH',"./resource/attach");
define('RESOURCE_URL',"http://{$_SERVER['HTTP_HOST']}/resource/attach");

/**
// 定义未捕获异常的处理函数
function ExceptionHandler($e) {
	    echo "<strong>Exception:</strong>",$e->getMessage(),"<br />\n";
		     echo "Stack Trace String:".$e->getTraceAsString();
}
// 设置用户自定义异常处理函数
set_exception_handler('ExceptionHandler');
 
function ErrorHandler($errno, $errmsg, $errfile, $errline){
	    if($errno == E_USER_ERROR) {
			         $msg = "<strong>Custom Error:</strong>$errmsg<br />\n";
						        $msg .= "File:$errfile<br />\n";
								          $msg .= "Line Number:$errline<br />\n";
											     }
		     echo $msg;
			      // 记录错误信息
			      error_log(date("[Y-m-d H:i:s]")." -[".$_SERVER['REQUEST_URI']."] :<br />\n".$msg."<hr />", 3, 'error_log.html');
					    //exit();
}
 
// set_error_handler()函数用于让用户自定义错误处理函数
// set_error_handler(error_function, error_type)
// error_function 必须, 制定发生错误时运行的函数
// error_type 可选, 规定不同的错误级别提示的不同信息, 默认是"E_ALL"
set_error_handler('ErrorHandler');
 
/*
	 * DEOM
	  * 
	   * throw new Exception('Uncaught Exception occurred.');
		 * 
		  * $foo = 2;
		   * 
			 * if ($foo > 1) {
			  *     // trigger_error()接收一个错误信息和一个常量作为参数, 
			   *     // 常量为 E_USER_ERROR -> a fatal error
				 *     //       E_USER_WARNING -> a non-fatal error
				  *     //       E_USER_NOTICE -> a report that may not represent an error 
				   *     trigger_error("A custom error has been trigglered", E_USER_ERROR);
					 * }
					  * 
					   */

?>
