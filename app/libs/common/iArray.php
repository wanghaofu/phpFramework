<?php
/******************************************************************

Name: 数组处理函数库

	 ******************************************************************/

class iArray
{
	
	
	// 获取上级数组
	// $array: 数组, $curKey: 当前值, $pIdField: 上级ID键名, $retField: 返回键名(空则返回整个数组)
	

	public static function array_get_parents($array, $curKey, $pIdField, $retField = null) {
		$newArr = $array;
		while ( list ( $key, $item ) = @each ( $array ) ) {
			if ($item [$pIdField]) {
				$newArr [$key] ['_parent'] = &$newArr [$item [$pIdField]];
			}
		}
		
		$arrayReturn = array ();
		$arrayItem = $newArr [$curKey];
		while ( $arrayItem ) {
			array_unshift ( $arrayReturn, $retField == null ? $arrayItem : $arrayItem [$retField] );
			$arrayItem = $arrayItem ['_parent'];
		}
		return $arrayReturn;
	}
	
	// 生成下拉菜单
	public static function make_tree_options($array, $idField, $pIdField, $textField, $valueField = '', $pIdValue = 0, $expect = 0, $depth = 0, $prefix = '&nbsp;&nbsp;', $dot = " &gt; ") {
		$arrayReturn = array ();
		
		if (! $valueField) {
			$valueField = $idField;
		}
		
		for($i = 0; $i < $depth; $i ++) {
			$prefixStr .= $prefix;
		}
		
		if (! is_array ( $selectedIds ) && $selectedIds != '') {
			$selectedIds = array ($selectedIds );
		}
		
		while ( list ( $key, $item ) = each ( $array ) ) {
			if ($item [$pIdField] == $pIdValue && $item [$valueField] != $expect) {
				$arrayReturn [$item [$valueField]] = "$prefixStr$dot{$item[$textField]}";
				if ($item [$idField] != $pIdValue) {
					$arrayReturn += _make_tree_options ( $array, $idField, $pIdField, $textField, $valueField, $item [$idField], $expect, $depth + 1, $prefix, $dot );
				}
			}
		}
		return $arrayReturn;
	}
	
	// 数组递归处理
	public static function deal_array($array, $call_bak   ) {
		while ( list ( $key, $item ) = @each ( $array ) ) {
			$array [$key] = is_array ( $item ) ? self::deal_array ( $item, $call_bak ) : call_user_func($call_bak , $item); // As of PHP 5.3.0;
		}
		@reset ( $array );
		return $array;
	}
	
	// 重构数组
	public static function array_format($array, $keyField = null, $valueField = null) {
		$newArray = array ();
		while ( list ( $key, $value ) = @each ( $array ) ) {
			$index = ! is_null ( $keyField ) ? $value [$keyField] : $key;
			if (is_null ( $valueField )) {
				$newArray [$index] = $value;
			} elseif (is_array ( $valueField )) {
				reset ( $valueField );
				while ( list ( $valueKey, $valueItem ) = @each ( $valueField ) ) {
					$newArray [$index] [$valueItem] = $value [$valueItem];
				}
			} else {
				$newArray [$index] = $value [$valueField];
			}
		}
		return $newArray;
	}
	
	// 随机抽取数组元素
	public static function array_rand($array) {
		$index = @array_rand ( $array );
		$ret = $array [$index];
		return $ret;
	}
	
	// 数组排序
	public static function array_sort($array, $keyFields = 0, $sortTypes = 'asc') {
		// $sortType —— 'asc': 升序 'desc': 降序
		

		$valueArray = array ();
		$newArray = array ();
		
		$keyField = is_array ( $keyFields ) ? current ( $keyFields ) : $keyFields;
		$sortType = is_array ( $sortTypes ) ? current ( $sortTypes ) : $sortTypes;
		
		while ( list ( $key, $item ) = @each ( $array ) ) {
			$valueArray [$key] = $item [$keyField];
		}
		
		$sortFunc = strtolower ( $sortType ) == 'desc' ? 'arsort' : 'asort';
		$sortFunc ( $valueArray );
		
		$lastItem = null;
		$i = 0;
		while ( list ( $key, $item ) = @each ( $valueArray ) ) {
			if (! is_null ( $lastItem ) && $array [$key] [$keyField] != $lastItem [$keyField])
				$i ++;
			$newArray [$i] [$key] = $array [$key];
			$lastItem = $array [$key];
		}
		
		if (@array_shift ( $keyFields )) {
			@array_shift ( $sortTypes );
			while ( list ( $key, $item ) = @each ( $newArray ) ) {
				if (count ( $item ) > 1) {
					$newArray [$key] = self::array_sort ( $item, $keyFields, $sortTypes );
				}
			}
			reset ( $newArray );
		}
		
		$retArray = array ();
		while ( list ( $key, $item ) = @each ( $newArray ) ) {
			while ( list ( $sKey, $sItem ) = @each ( $item ) ) {
				$retArray [$sKey] = $array [$sKey];
			}
		}
		return $retArray;
	}
	
	// 过滤数组
	public static function array_filter($prefix, $array = '', $trim = false) {
		if (! $prefix) {
			return $array;
		}
		$newArray = array ();
		while ( list ( $key, $item ) = @each ( $array ) ) {
			if (strstr ( $key, $prefix ) == $key) {
				if ($trim) {
					$key = preg_replace ( "/^$prefix/", '', $key );
				}
				$newArray [$key] = $item;
			}
		}
		return $newArray;
	}
	
	// 重置数组索引
	public static function array_reindex($array) {
		$newArray = array ();
		while ( list ( $key, $item ) = @each ( $array ) ) {
			$newArray [] = $item;
		}
		return $newArray;
	}
	
	// 二维数组中搜索满足条件的数组
	// $twoDim = false 时搜索一维数组
	public static function array_search($haystack, $condition = '', $limit = 0, $twoDim = true) {
		if (! $condition) {
			return $haystack;
		}
		
		if ($twoDim) {
			$replaceFrom = array ("|(\w+)[ ]*\!=|im", "|(\w+)[ ]*[=]+|im", "|(\w+)[ ]*>|im", "|(\w+)[ ]*<|im", 

			"| and |im", "| or |im", "| not |im", "/(\w+)[ ]*like[ ]*['|\"]%([^|]*)%['|\"]/ims" );
			$replaceTo = array ('$item[\'\1\'] !=', '$item[\'\1\'] ==', '$item[\'\1\'] >', '$item[\'\1\'] <', 

			' && ', ' || ', ' ! ', 'strstr ( strtolower ( $item[\'\1\'] ), strtolower ( "\2" ) ) != ""' );
			
			$condition = preg_replace ( $replaceFrom, $replaceTo, $condition );
		}
		
		while ( list ( $key, $item ) = @each ( $haystack ) ) {
			$con = $condition;
			$matched = false;
			
			if ($twoDim) {
				@eval ( "\$con = $con;" );
				if (is_bool ( $con )) {
					$matched = $con;
				}
			} else {
				$matched = ($item == $condition);
			}
			
			if ($matched) {
				$arrayReturn [$key] = $item;
			}
			
			if ($limit > 0 && count ( $arrayReturn ) >= $limit) {
				break;
			}
		}
		return $arrayReturn;
	}
	
	// 连接数组并忽略空键值
	public static function join($char, $array) {
		@reset ( $array );
		while ( list ( $key, $item ) = @each ( $array ) ) {
			if (strval ( $item ) == '') {
				unset ( $array [$key] );
			}
		}
		$str = @join ( $char, $array );
		return $str;
	}
	
	// 删除 $array 数组中不存在于 $arrayKeys 的键
	public static function array_fit($array, $arrayKeys) {
		while ( list ( $key, $item ) = @each ( $array ) ) {
			if (! in_array ( $key, $arrayKeys )) {
				unset ( $array [$key] );
			}
		}
		return $array;
	}
	
	// 字符串或数组是否包含某个值
	public static function contained($array, $string, $Split = ',') {
		if (! is_array ( $array )) {
			$array = explode ( $Split, $array );
		}
		
		return (in_array ( $string, $array ));
	}
	
	// 将二维数组中某一列构成一个新的数组
	public static function array_cols($array, $keyField) {
		$array_cols = array ();
		while ( list ( $key, $item ) = @each ( $array ) ) {
			$array_cols [] = $item [$keyField];
		}
		return $array_cols;
	}
	
	// 序列转换数组
	public static function serial_to_array($strSerial, $strSplitMain = ';', $strSplitSub = ':', $mergeNumeric = true) {
		$arrResult = array ();
		if ($strSerial) {
			$arrRand = explode ( $strSplitMain, $strSerial );
			while ( list ( $key, $item ) = @each ( $arrRand ) ) {
				if (! $item)
					continue;
				$arrItem = explode ( $strSplitSub, $item );
				$arrItem [0] = str_replace ( array ("\n", "\r" ), '', $arrItem [0] );
				
				// 是否合并数值型值
				if ($mergeNumeric && isset ( $arrResult [$arrItem [0]] ) && is_numeric ( $arrResult [$arrItem [0]] ) && is_numeric ( $arrItem [1] )) {
					$arrResult [$arrItem [0]] += $arrItem [1];
				} else {
					$arrResult [$arrItem [0]] = $arrItem [1];
				}
			}
		}
		return $arrResult;
	}
	
	// 数组转换序列
	public static function array_to_serial($array, $strSplitMain = ';', $strSplitSub = ':') {
		while ( list ( $key, $item ) = @each ( $array ) ) {
			$array [$key] = $key . $strSplitSub . $item;
		}
		$strSerial = _join ( $strSplitMain, $array );
		return $strSerial;
	}
	
	// 多重序列转换数组
	public static function multi_serial_to_array($strSerial, $lineSplit = "\n", $idSplit = '/', $strSplitMain = ';', $strSplitSub = ':', $mergeNumeric = true) {
		$arrResult = array ();
		if ($strSerial) {
			$strSerial = str_replace ( "\r", '', $strSerial );
			if ($strSerial && $entry = explode ( $lineSplit, $strSerial )) {
				while ( list ( $key, $item ) = @each ( $entry ) ) {
					$temp = explode ( $idSplit, $item );
					if ($temp [0])
						$arrResult [$temp [0]] = _serial_to_array ( $temp [1], $strSplitMain, $strSplitSub, $mergeNumeric );
				}
			}
		}
		return $arrResult;
	}
	
	// 数组转换多重序列
	public static function array_to_multi_serial($array, $lineSplit = "\n", $idSplit = '/', $strSplitMain = ';', $strSplitSub = ':') {
		while ( list ( $key, $item ) = @each ( $array ) ) {
			$array [$key] = $key . $idSplit . _array_to_serial ( $item, $strSplitMain, $strSplitSub );
		}
		$strSerial = _join ( $lineSplit, $array );
		return $strSerial;
	}
	
	// 去除数组中的空元素
	public static function array_trim_empty($array) {
		while ( list ( $key, $item ) = @each ( $array ) ) {
			if (is_array ( $item )) {
				$array [$key] = _array_trim_empty ( $item );
			} elseif (! $item) {
				unset ( $array [$key] );
			}
		}
		@reset ( $array );
		return $array;
	}
	
	// 字串转换数组
	public static function string_to_array($string, $strSplitMain = ';', $strSplitSub = ':') {
		$arrResult = array ();
		if ($string) {
			$arrRand = explode ( $strSplitMain, $string );
			while ( list ( $key, $item ) = @each ( $arrRand ) ) {
				$arrResult [] = explode ( $strSplitSub, $item );
			}
		}
		return $arrResult;
	}
	
	// 获取数组中键名大于等于参数值的最小元素
	public static function array_get_value($array, $keyValue) {
		while ( list ( $key, $item ) = @each ( $array ) ) {
			if ($keyValue < $key)
				break;
			$keyItem = $item;
		}
		return $keyItem;
	}
	# 把数组转化成对象
	public static function   array_to_object($data) {
		$ref = new stdClass ();
		if (is_array ( $data )) {
			foreach ( $data as $key => $val ) {
				$ref->$key = array_to_object ( $val );
			}
		} else {
			$ref = $data;
		}
		return $ref;
	}
}
?>