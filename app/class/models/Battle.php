<?php
/**
 * @name 作战API
 * @access public
 * $author chenliang
 * $date 2012-02
 */

class Battle extends Init 
{
	public function __construct( $uuId = 0 )
	{
		parent::__construct( $uuId );
	}
	
	/**
	 * 激活新作战
	 */
	public function newBattle( $battle_id )
	{
		$this->_udb->set('uuid', $this->uuid);
		$this->_udb->set('battle_id', $battle_id);
		$this->_udb->dataInsert('user_battles');
		$this->_uCache->flush('userBattles');
	}
	
	/**
	 * 更新作战进度
	 * @param $battle_id
	 */
	public function updateBattle( $battle_id )
	{
		$this->_udb->set('schedule', 'schedule+10', false);
		$this->_udb->dataUpdate('user_battles', "uuid=$this->uuid AND battle_id=$battle_id");
	}
	
	/**
	 * 更新通关进度
	 * 激活boss战、boss战胜、PVP战胜情况下会用到此方法
	 * @param $battle_id
	 * @param $status default:0 黄
	 * 1:boss战开启(黄)   2:boss战胜利(绿)   3:PVP战胜利(红)
	 */
	public function doBattle( $battle_id, $status )
	{
		switch ($status)
		{
			case 1:
				$this->_udb->set('status', 1);
				break;
			case 2:
				//验证PVP战是否都完成...
				//$status = 3;
				$this->_udb->set('status', 2);
				break;
			case 3:
				//验证普通任务是否都完成...
				//return false;
				$this->_udb->set('status', 3);
				break;
		}
		if($status) {
			$this->_udb->dataUpdate("user_battles", "uuid=$this->uuid AND battle_id=$battle_id");
		}
		return true;
	}
}
?>