<?php
/**
 * @name 用户信息API
 * @access public
 * $author chenliang
 * $date 2012-02
 */

class Session extends Init 
{
	public function __construct( $uuId = 0 )
	{
		parent::__construct( $uuId );
	}
	
	/**
	 * 增加经验值
	 */
	public function gainExp() {}
	
	/**
	 * 升级
	 */
	public function levelUp() {}
	
	/**
	 * 更新用户资金
	 * @param $value
	 * @param $isChange 值为true时$value为差值,$value为负数表示减少的意思
	 */
	public function updateCoin( $value, $isChange = false )
	{
		if(!$isChange) {
			$this->_udb->set('coin', $value);
		} else {
			$this->_udb->set('coin', "coin+$value", false);
		}
		$this->_udb->dataUpdate('user_session',"uuid=$this->uuid");
	}
	
	/**
	 * 更新用户行军值
	 * @param $value
	 * @param $isChange 值为true时$value为差值,$value为负数表示减少的意思
	 */
	public function updateEnergy( $value, $isChange = false )
	{
		if(!$isChange) {
			$this->_udb->set('energy', $value);
		} else {
			$this->_udb->set('energy', "energy+$value", false);
		}
		$this->_udb->dataUpdate('user_session',"uuid=$this->uuid");
	}
	
	/**
	 * 更新其他属性
	 */
	public function updateSession($properties, $value) {}
}
?>