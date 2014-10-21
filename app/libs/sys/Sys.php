<?php
//namespace factory;
//自动载入
//spl_autoload_register('sys::autoLoad'); // As of PHP 5.3.0


class sys {
	// }}}
	// ----[ Class Constants ]----------------------------------------
	// {{{ SEPARATOR
	const SEPARATOR_PHP_NS = '\\';
	const SEPARATOR_SCHEMA = '#';
	const SEPARATOR_DIRECTORY = '/';
	const SEPARATOR_VAR_NAME_NEST = '.';
	const SEPARATOR_SECTION_NAME = ':';
	
	// }}}
	
	
	static $objects = array ();
	public static /* void */
        function registerAutoload(/* string */ $dir_path,
                                  /* string */ $prefix = '',
                                  /* string */ $file_ext = '.php') {
		SystemClassLoader::register ( $dir_path, $prefix, $file_ext );
	}
	/**  PHP 5.3.0之后版本  */
	public static function __callStatic($className, $arguments) {
		// Note: value of $name is case sensitive.
		//		echo "Calling static method '$name' " . implode ( ', ', $arguments ) . "\n";
		SystemClassLoader::load ( $className );
		//
		$obj_key = null;
		if ($arguments) {
			$obj_arg = implode ( '_', $arguments );
			$obj_key = $className . '#' . $obj_arg;
		} else {
			$obj_key = $className;
		}
		
		if (is_object ( self::$objects [$obj_key] )) {
			return self::$objects [$obj_key];
		} elseif (class_exists ( $className )) {
			$arg_num = count ( $arguments );
			switch ($arg_num) {
				case 1 :
					self::$objects [$obj_key] = new $className ( $arguments [0] );
					break;
				case 2 :
					self::$objects [$obj_key] = new $className ( $arguments [0], $arguments [1] );
					break;
				case 3 :
					self::$objects [$obj_key] = new $className ( $arguments [0], $arguments [1], $arguments [2] );
					break;
				default :
					self::$objects [$obj_key] = new $className ();
			}
		}
		//		is_object
		return self::$objects [$obj_key];
	}
	
	public static function data($moduleName) {
	
	}
	//out alis
	public static function ac($moduleName) {
	
	}
	//out alis
	public static function assign($key, $value) {
	
	}
	public static function Exception($errorId) {
	
	}

	//	static public function out()
//	{
//		$out = new out;
//		return $out;
//	}
}
require_once 'libs/sys/SystemFactory.php';
// ----[ Autoload ]---------------------------------------------------
// Autoloaderの設定
require_once 'libs/sys/SystemClassLoader.php';
spl_autoload_register ( 'SystemClassLoader::load' );

//class init dir
sys::registerAutoload ( CLASS_SRC_ROOT );
//module init dir
sys::registerAutoload ( MODEL_SRC_ROOT);
//lib root 
sys::registerAutoload ( LIBS );
//common lib dir
sys::registerAutoload ( LIB_COMMON );
//lib dir
sys::registerAutoload ( LIB_IDATA );
//idata 


?>
