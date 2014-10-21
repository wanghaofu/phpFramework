<?php

/***************************************************************

	Name: 语言包载入库 ( 基于数据库类和缓存类 )
	Author: 王涛
	Email: wanghaofu@163.com
	QQ: 595900598

	Update Log:
		2008/03/08 by wangtao

 ***************************************************************/

if (! defined ( 'IN_SYSTEM' )) {
	exit ( 'Access Denied' );
}

class Language {
	var $data = array (); // 语言数据
	

	// 构造函数
	function Language($language) {
		$this->language = $language;
	}
	
	// 初始化
	function init($packages = null) {
		$this->load ( 'global' );
		
		if (! is_null ( $packages )) {
			if (! is_array ( $packages )) {
				$this->load ( $packages );
			} else {
				while ( list ( $key, $item ) = @each ( $packages ) ) {
					$this->load ( $item );
				}
			}
		}
	}
	
	// 载入语言包
	function load($package) {
		$cacheKey = 'lang__' . $this->language . '__' . $package;
		$cacheSetting = array (
			'selectFrom' => "languages WHERE  package='$package' ",
			 'keyField' => 'name',
		     'valueField' => 'value', 
		     'lifeTime' => 0, 
		     'noSync' => true
		);
		
		stra::set ( $cacheKey, $cacheSetting );
		$lang = stra::ac ( $cacheKey );
		if (is_array ( $lang )) {
			$this->data = array_merge ( $this->data, $lang );
			tpl::assign ( 'lang', $this->data );
		}
	}
	
	// 输出文字
	function show($key, $replaceTo = null) {
		$text = $this->data [$key];
		if (is_array ( $text )) {
			$text = iArray::array_rand ( $text );
		}
		if (is_array ( $replaceTo )) {
			$text = String::batch_replace ( $text, $replaceTo );
		} else {
			$text = str_replace ( '%0', $replaceTo, $text );
		}
		return $text;
	}
	function __get($key) {
		return $this->show ( $key, $replaceTo );
	}
}
/**
 * 
 */

$config = stra::ac ( 'config' );
$lang = new Language ( $config ['default_language'] );
$lang->init ();
stra::initLang ();

?>