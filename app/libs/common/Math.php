<?php

/******************************************************************

	Name: 数学处理函数库
	Author: 王涛 ( tony )
	Email: wanghaofu@163.com
	QQ: 595900598

 ******************************************************************/
class Math {
	// 数值转等级
	public static function value_to_level($value) {
		$level = 0;
		$levelValue = 1;
		while ( $value >= $levelValue ) {
			$level ++;
			$levelValue = pow ( $level + 1, 3 );
		}
		return $level;
	}
	
	public static function value_to_level_2($value) {
		$level = 1;
		while ( $value >= 0 ) {
			$value -= pow ( $level + 1, 2 );
			if ($value >= 0) {
				$level ++;
			}
		}
		return ($level - 1);
	}
	
	// 根据数组值作为概率进行随机选择
	public static function get_probability_key($array, $except = null, $degree = 100) {
		if (! is_null ( $except )) {
			unset ( $array [$except] );
		}
		$total = @array_sum ( $array ) * $degree;
		if (! $total) {
			return false;
		}
		$intRand = mt_rand ( 0, $total );
		$offset = 0;
		while ( list ( $key, $item ) = @each ( $array ) ) {
			$value = $item * $degree;
			if ($intRand <= $value + $offset) {
				$result = $key;
				break;
			}
			$offset += $value;
		}
		return $result;
	}
	
	// 根据概率值返回是否成功
	// $value : 概率值 ( 百分比值，如：30 表示 30% )
	// $degree : 精度倍数
	public static function probability($value, $degree = 100) {
		if ($value <= 0)
			return false;
		$value = $value * $degree;
		$rand = mt_rand ( 1, $degree * 100 );
		return ($rand <= $value);
	}
}
?>