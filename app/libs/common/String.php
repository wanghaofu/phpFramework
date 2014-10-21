<?php

/******************************************************************

	Name: 字符串处理函数库

 ******************************************************************/
class String {
	// 调试函数
	public static function debug($var, $color = null) {
		if (! is_null ( $color ))
			echo ("<font color='" . $color . "'>");
		echo ('<pre>');
		print_r ( $var );
		echo ('</pre>');
		if (! is_null ( $color ))
			echo ("</font>");
	}
	
	// 序列化数组
	public static function serialize($array) {
		$array = iArray::deal_array ( $array, 'urlencode' );
		$str = serialize ( $array );
		return $str;
	}
	
	// 反序列化
	public static function unserialize($str) {
		$array = unserialize ( $str );
		$array = iArray::deal_array ( $array, 'urldecode' );
		$array = iArray::deal_array ( $array, 'stripslashes' );
		@reset ( $array );
		return $array;
	}
	
	// 不可逆加密
	public static function encode($str) {
		$str = md5 ( md5 ( $str ) . md5 ( SYSTEM_CODE ) );
		return $str;
	}
	
	// 截取字符串并添加省略后缀
	public static function cut($str, $length, $suffix = '...') {
		$str = mb_strlen ( $str ) > $length ? (_msubstr ( $str, 0, $length - 2 ) . $suffix) : $str;
		return ($str);
	}
	
	// 中文字符串截取
	public static function msubstr($str, $start, $length) {
		if (function_exists ( 'mb_substr' )) {
			$charSet = defined ( 'DEFAULT_CHARSET' ) ? DEFAULT_CHARSET : '';
			$tmpStr = mb_substr ( $str, $start, $length, $charSet );
		} else {
			$strlength = $start + $length;
			for($i = $start; $i < $strlength; $i ++) {
				if (ord ( substr ( $str, $i, 1 ) ) > 0xa0) {
					$tmpStr .= substr ( $str, $i, 2 );
					$i ++;
				} else {
					$tmpStr .= substr ( $str, $i, 1 );
				}
			}
		}
		return $tmpStr;
	}
	
	// 关键字高亮
	public static function keyword_highlight($str, $keyword, $prelength = 50) {
		$strPos = strpos ( $str, $keyword );
		
		if ($strPos || strstr ( $str, $keyword )) {
			if ($prelength == 0) {
				$prelength = strlen ( $str );
			}
			
			$sub = substr ( $str, $strPos - $prelength, $prelength );
			preg_match_all ( "/[!-~]+/", $sub, $temp );
			
			if (strlen ( $sub ) < $prelength) {
				$normalCharPos = $strPos;
			} else {
				$pre = "...";
				if (count ( $temp [0] ) > 0) {
					$normalCharPos = $prelength - strrpos ( $sub, $temp [0] [count ( $temp [0] ) - 1] );
				} else {
					$splitArray = array ("。", "，", "、", "；", "“", "”" );
					while ( 1 ) {
						$normalCharPos = strrpos ( $sub, current ( $splitArray ) );
						if ($normalCharPos > 0) {
							$normalCharPos = $prelength - $normalCharPos - 2;
							break;
						}
						if (! next ( $splitArray )) {
							break;
						}
					}
				}
			}
			$str = $pre . substr ( $str, $strPos - $normalCharPos, strlen ( $str ) );
		}
		return $str;
	}
	
	// 生成密码
	public static function password($str) {
		$password = md5 ( $str );
		return $password;
	}
	
	// Unicode 解码
	public static function unescape($str, $charCode = null) {
		if (is_null ( $charCode )) {
			$charCode = DEFAULT_CHARSET;
		}
		
		$text = preg_replace_callback ( "/%u[0-9A-Za-z]{4}/", toUtf8, $str );
		return mb_convert_encoding ( $text, $charCode, 'utf-8' );
	}
	
	public static function  toUtf8($ar) {
		foreach ( $ar as $val ) {
			$val = intval ( substr ( $val, 2 ), 16 );
			if ($val < 0x7F) // 0000-007F
{
				$c .= chr ( $val );
			} elseif ($val < 0x800) // 0080-0800
{
				$c .= chr ( 0xC0 | ($val / 64) );
				$c .= chr ( 0x80 | ($val % 64) );
			} else // 0800-FFFF
{
				$c .= chr ( 0xE0 | (($val / 64) / 64) );
				$c .= chr ( 0x80 | (($val / 64) % 64) );
				$c .= chr ( 0x80 | ($val % 64) );
			}
		}
		return $c;
	}
	
	// 生成唯一字符串
	public static function make_uniqid_code($length = 0) {
		$arrayReturn = md5 ( uniqid ( mt_rand () . _get_ip (), true ) );
		$arrayReturnLen = strlen ( $arrayReturn );
		if ($length > 0 && $length < $arrayReturnLen) {
			$arrayReturn = substr ( $arrayReturn, ($arrayReturnLen - $length) / 2, $length );
		}
		return $arrayReturn;
	}
	
	// 转换字符编码
	public static function charset($string, $charSet = '') {
		$from = DEFAULT_CHARSET;
		$charSet = $charSet ? $charSet : SYSTEM_CHARSET;
		if ($from != $charSet) {
			$string = mb_convert_encoding ( $string, $charSet, $from );
		}
		return $string;
	}
	
	// 字符串加密
	public static function encrypt($string, $key) {
		srand ( ( double ) microtime () * 1000000 );
		$encryptKey = md5 ( mt_rand ( 0, 32000 ) );
		$ctr = 0;
		$tmp = "";
		for($i = 0; $i < strlen ( $string ); $i ++) {
			if ($ctr == strlen ( $encryptKey )) {
				$ctr = 0;
			}
			$tmp .= substr ( $encryptKey, $ctr, 1 ) . (substr ( $string, $i, 1 ) ^ substr ( $encryptKey, $ctr, 1 ));
			$ctr ++;
		}
		return base64_encode ( _keyed ( $tmp, $key ) );
	}
	
	// 字符串解密
	public static function decrypt($string, $key) {
		$string =self::keyed ( base64_decode ( $string ), $key );
		$tmp = "";
		for($i = 0; $i < strlen ( $string ); $i ++) {
			$md5 = substr ( $string, $i, 1 );
			$i ++;
			$tmp .= (substr ( $string, $i, 1 ) ^ $md5);
		}
		return $tmp;
	}
	
	public static function keyed($string, $encryptKey) {
		$encryptKey = md5 ( $encryptKey );
		$ctr = 0;
		$tmp = "";
		for($i = 0; $i < strlen ( $string ); $i ++) {
			if ($ctr == strlen ( $encryptKey )) {
				$ctr = 0;
			}
			$tmp .= substr ( $string, $i, 1 ) ^ substr ( $encryptKey, $ctr, 1 );
			$ctr ++;
		}
		return $tmp;
	}
	
	// 文本加密
	public static function encrypt_string($string, $code = '') {
		if (! $code)
			$code = GAME_CODE;
		$string = md5 ( $string . $code );
		return $string;
	}
	
	// 斜杆和引号前加转义符
	public static function add_slashes($string, $trimReturns = true, $quote = 'single') {
		if (is_array ( $string )) {
			reset ( $string );
			while ( list ( $key, $item ) = @each ( $string ) ) {
				$string [$key] = self::add_slashes ( $item, $trimReturns, $quote );
			}
			reset ( $string );
		} else {
			$cQuote = $quote != 'single' ? '"' : "'";
			if ($trimReturns) {
				$string = str_replace ( array ("\n", "\r" ), '', $string );
			}
			$string = str_replace ( array ("\\", $cQuote ), array ("\\\\", "\\" . $cQuote ), $string );
		}
		return $string;
	}
	
	// 去除转义符
	public static function strip_slashes($string, $quote = 'single') {
		if (is_array ( $string )) {
			reset ( $string );
			while ( list ( $key, $item ) = @each ( $string ) ) {
				$string [$key] = self::strip_slashes ( $item, $quote );
			}
		} else {
			$cQuote = $quote != 'single' ? '"' : "'";
			$string = str_replace ( array ("\\\\", "\\" . $cQuote ), array ("\\", $cQuote ), $string );
		}
		return $string;
	}
	
	// 处理为正则字符串
	public static function to_reg_pattern($string, $fullPattern = false) {
		if (is_array ( $string )) {
			reset ( $string );
			while ( list ( $key, $item ) = @each ( $string ) ) {
				$string [$key] = self::to_reg_pattern ( $item, $fullPattern );
			}
		} else {
			$string = str_replace ( array ('/', '(', ')', '[', ']', '.', '*' ), array ('\/', '\(', '\)', '\[', '\]', '\.', '.*' ), $string );
			
			if ($fullPattern) {
				$string = '/' . $string . '/im';
			}
		}
		return $string;
	}
	
	// 生成哈希分布规则
	public static function hash($str, $level = 1, $length = 2) {
		$hash = hash ( 'md5', strtolower ( $str ) );
		for($i = 0; $i < $level; $i ++) {
			$hashParts [] = substr ( $hash, $i * $length, $length );
		}
		$hash = join ( '/', $hashParts );
		return $hash;
	}
	
	// 检查非法字符
	public static function invalid_chars($str) {
		if (ereg ( "[\\\/\:\*\?\"\'<>\|]", $str )) {
			return '\ / | : * ? " \' < >';
		} else {
			return false;
		}
	}
	
	// 获取错误信息
	public static function get_error($str, $errorFlag = '<ERROR>') {
		$errorLength = strlen ( $errorFlag );
		if (substr ( $str, 0, $errorLength ) == $errorFlag) {
			$errorMsg = substr ( $str, $errorLength, strlen ( $str ) );
			return $errorMsg ? $errorMsg : true;
		} else {
			return false;
		}
	}
	
	// 生成错误信息
	public static function make_error($str = '', $errorFlag = '<ERROR>') {
		$errorMsg = $errorFlag . $str;
		return $errorMsg;
	}
	
	// 输出错误信息
	public static function error($msg) {
		echo (self::make_error ( $msg ));
		exit ();
	}
	
	// 交换两个值
	public static function swap(&$varFrom, &$varTo) {
		$temp = $varFrom;
		$varFrom = $varTo;
		$varTo = $temp;
	}
	
	// 公式计算
	// $formula: 公式
	// $assignVars : 变量赋值
	public static function formula($formula, $assignVars) {
		if (! trim ( $formula ))
			return null;
		extract ( $assignVars );
		eval ( "\$formulaResult = ( $formula );" );
		return $formulaResult;
	}
	
	// 批量替换文字
	// 按顺序将 %0  %1  %2 ... 替换为数组中的值
	public static function batch_replace($text, $replaceTo) {
		$replaceFrom = $replaceToTmp = array ();
		for($i = 0; $i < count ( $replaceTo ); $i ++) {
			$replaceFrom [] = "%$i";
			$replaceToTmp [] = "<!--$i-->";
		}
		$text = str_replace ( $replaceFrom, $replaceToTmp, $text );
		$text = str_replace ( $replaceToTmp, $replaceTo, $text );
		return $text;
	}
}
?>