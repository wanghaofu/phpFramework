<?php
/****** 模板初始化操作 ******/
/****** 局部函数 ******/
/****** 主程序 ******/
class tpl {
	
	static $tpl;
	static $var=array();
	
	
	function __set($name,$value)
	{
//		tpl::val('sdf',234);
//		$tpl->xx=23;
//		$tpl->
	}
	
	//判断是否在线
	static function get_club_online_status($clubId) {
		global $gCache, $gDate;
		
		$clubId = intval ( $clubId );
		
		$setting = $gCache->getData ( 'setting' );
		
		$clsClub = new clsClub ();
		if (! $clubId)
			return false;
		
		$clsClub->init ( $clubId );
		$clubInfo = $clsClub->getClubInfo ();
		if ($clubInfo ['reset'] == 2) {
			return - 1; // 已封禁
		}
		$clubRecords = $clsClub->getClubRecords ();
		return ($gDate->value - $clubRecords ['last_action_time'] <= $setting ['online_check_time']);
	}
	
	// 输出操作完成消息框
	static function show_message($message, $scriptCode = null, $scriptText = '', $system = true) {
		
		$scriptCode = 'dialog.close(this);' . $scriptCode;
		self::$tpl->assign ( array ('message' => $message, 'scriptCode' => $scriptCode, 'scriptText' => $scriptText ) );
		self::$tpl->display ( 'system/dialog_message.html' );
		exit ();
	}
	
	// 输出提示对话框
	static function show_dialog($errorMsg, $scriptCode = null, $scriptText = null, $system = false) {
		//	echo _make_error ( '' );
		show_alert ( $errorMsg, $scriptCode, $scriptText, $system );
		exit ();
	}
	// 输出确认框
	static function show_confirm($message, $scriptCode = null, $scriptText = null, $system = true) {
		
		$addition = 'dialog.close(this);';
		if (! is_array ( $scriptCode )) {
			$scriptCode = array ($addition . $scriptCode, $addition );
		} else {
			while ( list ( $key, $item ) = @each ( $scriptCode ) ) {
				$scriptCode [$key] = $addition . $item;
			}
		}
		self::$tpl->assign ( array ('message' => $message, 'scriptCode' => $scriptCode, 'scriptText' => $scriptText ) );
		show ( 'system/dialog_confirm.html' );
	}
	
	// 输出对话框
	static function show_alert($message, $scriptCode = null, $scriptText = null, $system = true) {
		global $tpl;
		
		$scriptCode = 'dialog.close(this);' . $scriptCode;
		
		self::$tpl->assign ( array ('message' => $message, 'scriptCode' => $scriptCode, 'scriptText' => $scriptText ) );
		self::$tpl->display ( 'system/dialog_alert.html' );
	}
	
	
	
	static function show($tplname, $cache_lifetime = 3600) {
		global  $userInfo, $data, $tplVars ,$tplvar;
		is_array($tplVars) ?: array();
	
		empty ( $tplVars ) and  $tplVars = $data and  $tplVars=$tplvar ;
		$tplVars =(  !empty( $data ) && is_array($data) ) ? array_merge($tplVars , $data) : $tplVars ;
		$tplVars = ( !empty( $tplvar ) && is_array($tplvar) ) ? array_merge($tplVars , $tplvar) : $tplVars;
		if ($tplVars) {
			foreach ( $tplVars as $key => $value ) {
			 $value  =	( CONVERT_ENCODING_SETTING != DEFAULT_DB_CHAREST ) ? iArray::deal_array($value,'tpl::utoj') : $value ;
				
				if (is_numeric ( $key )) {
					global $$value;
					self::$tpl->assign ( $value, $$value );
				} else {
					self::$tpl->assign ( $key, $value );
				}
			}
		}
		self::$tpl->assign('StaticBaseUrl',STATIC_BASE_URL);
		self::$tpl->assign('language',BROWSER_LANGUAGE);
		self::$tpl->assign('$baseUrl',BASE_URL);
		self::$tpl->display ( $tplname );
	}
	static function utoj($value)
	{
		return mb_convert_encoding($value,CONVERT_ENCODING_SETTING,DEFAULT_DB_CHAREST);
		
	}
	static public function assign($key,$value)
	{
		self::$tpl->assign($key,$value);
	}
	/****** 
	 * 初始化smarty 模板引擎
	 * * 
	 * *
	 ***/
	
	static public function initSmarty() {
		// 配置模板类
		include_once (TPL_CLASS_DIR . "/Smarty.class.php");
		$tpl = new Smarty ();
		
		$tpl->template_dir = TPL_PATH . '/';
		$tpl->compile_dir = TEMPLATES_C;
		$tpl->config_dir = TEMPLATES_C;
		$tpl->cache_dir = TEMPLATES_C;
		$tpl->left_delimiter = "{{";
		$tpl->right_delimiter = "}}";
		$tpl->caching = false;
//		$tpl->security = true;
//		$tpl->security = false;
		
		// 自定义函数
//		$tplFunctions = array ('make_club_link' => 'make_club_link' );
		
//		while ( list ( $key, $item ) = @each ( $tplFunctions ) ) {
//			$tpl->register_function ( $key, $item );
//		}
		
		// 自定义修饰
//		$tplModifiers = array ('format_size' => '_format_size', 'add_slashes' => '_add_slashes', 'strip_slashes' => '_strip_slashes' );
//		
//		$tpl->security_settings ['MODIFIER_FUNCS'] = array_merge ( $tpl->security_settings ['MODIFIER_FUNCS'], $tplModifiers );
//		while ( list ( $key, $item ) = @each ( $tplModifiers ) ) {
//			$tpl->register_modifier ( $key, $item );
//		}
//		
//		array_push ( $tpl->secure_dir, TPL_PATH );
		
		return self::$tpl = $tpl;
	}
	/**
	 * 初始化ktpl模板引擎
	 * Enter description here ...
	 */
	static function initKtpl() {
		
		// 配置模板类 包含模板文件
		include_once (KTPL_DIR . 'cms.class.php');
		include_once (KTPL_DIR . 'kTemplate.class.php');
		include_once ('./libs/functions/function_Ktemplate.php');
		$params = array ('template_dir' => TPL_PATH, // 模板目录
'compile_dir' => TEMPLATES_C, //模板编译目录
'cache_dir' => CACHE_PATH, // 缓存路径
'lang_dir' => '', //语言包路径
'caching' => '0' );
		
		$tpl = new kTemplate ( $params );
		$tpl->registerPreFilter ( "CMS_Parser" );
		return self::$tpl = $tpl;
	}
	static function display()
	{
		self::$tpl->display();
	}
}
class response
{
	
}
// header ( "Content-Type: text/html; charset=" . DEFAULT_CHARSET );

// 是否开启 gzip 压缩
$gGZipEnabled = $gCache->data ['config'] ['enable_gzip'] && ! $_GET ['nogzip'] && ereg ( 'gzip', $_SERVER ['HTTP_ACCEPT_ENCODING'] );
if ($gGZipEnabled && ! count ( $_POST )) {
	ob_start ( 'ob_gzhandler' );
}





// 初始化语言包
//include_once ('./libs/lib_init_language.php');

//$tpl = tpl::initKtpl();
//de($tpl);
$tpl = tpl::initSmarty();






?>