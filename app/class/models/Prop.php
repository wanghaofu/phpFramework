<?php
/**
 * @name 静态数据访问模块
 * @author chenliang $2012-02
 */

/**
 * 
 * @example 
 * Prop::init('soldier')->getAll();
 * prop::init('soldier')->getOne();
 */

class Prop
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
?>