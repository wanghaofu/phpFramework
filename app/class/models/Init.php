<?php

/**
 * @name module层基类
 * 用于常用模块的继承
 * @author chenliang $2012-02
 */


class Init
{
	/**
	 * 用户ID
	 * @var int
	 */
	public $uuid;
	
	/**
	 * 用户DB资源
	 * @var obj
	 */
	protected $_udb = NULL;
	
	/**
	 * 用户Cache资源
	 * @var obj
	 */
	protected $_uCache = NULL;
	
	/**
	 * 大战略专用db资源
	 * @var obj
	 */
	protected $_stra_udb = NULL;
	
	/**
	 * 大战略专用cache资源
	 * @var obj
	 */
	protected $_stra_uCache = NULL;
	
	/**
	 * 大战略模式标记
	 * @var int
	 */
	protected $straMode = 0;
	
	/**
	 * 初始化
	 *
	 * @param int $uuid
	 */
	public function __construct( $uuid = 0 )
	{
		$this->uuid = $uuid==0 ? stra::$uuid : $uuid;
		$this->_udb = stra::uDb( $this->uuid );
		$this->_uCache = stra::uCache( $this->uuid );
		$this->_stra_udb = stra::db('stra_stra');
		$this->_stra_uCache = stra::createCache('stra_stra');
	}
}
?>