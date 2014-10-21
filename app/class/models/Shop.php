<?php
/**
* @description	商店models
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/29
* @modifyTime
				2012/03/29	文件创建	朱勇
*/

class Shop
{
	private $_propType;
	
	private static $_instance = NULL;
	
	public function __construct( $_propType )
	{
		$this->_propType = $_propType;
	}
	
	public function getAll()
	{
		return stra::ac( $this->_propType );
	}
	
	public function getOne( $_id )
	{
		$res = stra::ac( $this->_propType );
		return $res[ $_id ];
	}
	
	public static function init( $_propType )
	{
		if(!self::$_instance instanceof self || self::$_instance->_propType != $_propType) {
			self::$_instance = new self ( $_propType );
		}
		return self::$_instance;
	}
}

/* End of file Shop.php */
/* Location: /class/models/Shop.php */