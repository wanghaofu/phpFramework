<?php
/**
* @description	友军记录models
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/26
* @modifyTime
				2012/03/26	文件创建	朱勇
*/

class Friend extends Init {
	/**
	* 玩家ID
	*/
	public $uuid = NULL;
	
	/**
	 * 构造函数
	 *
	 * @param
	 * @return
	 */
	public function __construct( $uuid = 0 )
	{
		parent::__construct( $uuid );

		$this->uuid = $uuid;

		$cacheVars = array (
			'friend_send' => array (
				'selectFrom' => "friend_send WHERE uuid='{$uuid}'",
				'first' => FALSE,
				'lifeTime' => 60*60*24,
			),
			'friend_receive' => array (
				'selectFrom' => "friend_receive WHERE uuid='{$uuid}'",
				'first' => FALSE,
				'lifeTime' => 60*60*24,
			),
		);

		while ( list ( $key, $item ) = @each ( $cacheVars ) )
		{
			stra::uCache($uuid)->addData ( $key, $item );
		}
	}

	/**
	 * 获得所有发送请求
	 *
	 * @param
	 * @return array 发送请求
	 */
	public function getAllSend()
	{
		$getAllSend = $this->_uCache->getData('friend_send');

		return $getAllSend;
	}

	/**
	 * 获得所有接收请求
	 *
	 * @param
	 * @return array 接收请求
	 */
	public function getAllReceive()
	{
		$getAllReceive = $this->_uCache->getData('friend_receive');

		return $getAllReceive;
	}

	/**
	 * 发送友军请求
	 *
	 * @param array $param 参数列表
	 */
	public function Send( $param )
	{
		if( ! $param['uuid'])
		{
			$param['uuid'] = $this->uuid;
		}
		
		$this->_udb->addData('uuid', $param['uuid']);
		$this->_udb->addData('friend_id', $param['friend_id']);
		$this->_udb->addData('content', $param['content']);
		$this->_udb->addData('addtime', time());
		$this->_udb->addData('status', 0);
		
		$this->_udb->dataInsert('friend_send');
		$this->_uCache->flush('friend_send');
	}

	/**
	 * 接收友军请求
	 *
	 * @param array $param 参数列表
	 */
	public function Receive( $param )
	{
		if( ! $param['uuid'])
		{
			$param['uuid'] = $this->uuid;
		}
		
		$this->_udb->addData('uuid', $param['uuid']);
		$this->_udb->addData('friend_id', $param['friend_id']);
		$this->_udb->addData('content', $param['content']);
		$this->_udb->addData('addtime', time());
		$this->_udb->addData('status', 0);
		
		$this->_udb->dataInsert('friend_receive');
		$this->_uCache->flush('friend_receive');
	}
}

/* End of file Friend.php */
/* Location: /class/models/Friend.php */