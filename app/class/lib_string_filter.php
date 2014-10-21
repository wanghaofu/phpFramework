<?php
	 
/******************************************************************

	Name: 字符串过滤函数库
	Author: 

/******************************************************************/

if ( !defined ( 'IN_SYSTEM' ) )
{
	exit ( 'Access Denied' );
}

// 检查字符串是否合法
/*
	$arrFields 格式:
		array (
			Post变量名1 => 显示名称1,
			Post变量名2 => 显示名称2,
		);
*/

function string_check ( $arrFields, $arrCheck = null, $lengthCheck = null, $stringFilters = null )
{
	global $gCache, $lang;

	$lang->load ( 'string_filter' );

	if ( is_null ( $arrCheck ) )
	{
		$arrCheck = $_POST;
	}

	if ( !$lengthCheck ) $lengthCheck = $gCache->getData ( 'setting_global' );
	if ( !$stringFilters ) $stringFilters = $gCache->getData ( 'string_filters' );

	while ( list ( $key, $item ) = @each ( $arrFields ) )
	{
		$lengthInfo = @explode ( ',', $lengthCheck['length_' . $key] );

		$arrCheck[$key] = strtolower ( $arrCheck[$key] );
		if ( $lengthInfo[0] > 0 && trim ( $arrCheck[$key] ) == '' )
		{
			return _make_error ( $lang->show ( 'require_string_value', array ( $item ) ) );
		}

		if ( $text = _invalid_chars ( $arrCheck[$key] ) )
		{
			return _make_error ( $lang->show ( 'contain_invalid_chars', array ( $item, $text ) ) );
		}

		if ( $lengthInfo )
		{
			$strLength = strlen ( $arrCheck[$key] );
			if ( $lengthInfo[0] > 0 && $strLength < $lengthInfo[0] )
			{
				return _make_error ( $lang->show ( 'string_too_short', array ( $item, $lengthInfo[0] ) ) );
			}
			elseif ( $lengthInfo[1] > 0 && $strLength > $lengthInfo[1] )
			{
				return _make_error ( $lang->show ( 'string_too_long', array ( $item, $lengthInfo[1] ) ) );
			}
		}

		@reset ( $stringFilters );
		while ( list ( $text, $filterInfo ) = @each ( $stringFilters ) )
		{
			$text = strtolower ( $text );
			if ( $filterInfo['check_py'] && function_exists ( '_hz2py' ) )
			{
				$pyCheck = _hz2py ( _charset ( $arrCheck[$key], 'GB2312' ) );
				$pyFilter = _hz2py ( _charset ( $text, 'GB2312' ) );
				$invalidPy = strstr ( $pyCheck, $pyFilter );
			}
			else
			{
				$invalidPy = false;
			}
			if ( @strstr ( $arrCheck[$key], $text ) || $invalidPy )
			{
				return _make_error ( $lang->show ( 'contain_invalid_chars', array ( $item, $text ) ) );
			}
		}
	}
}

?>