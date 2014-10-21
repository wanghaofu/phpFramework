<?php
/**
* @description	消息记录models
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/26
* @modifyTime
				2012/03/26	文件创建	朱勇
*/

class Message extends Init {
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
			'hello' => array (
				'selectFrom' => "user_message WHERE uuid='{$uuid}' AND type='1'",
				'first' => FALSE,
				'lifeTime' => 60*60*24,
			),
			'message' => array (
				'selectFrom' => "user_message WHERE uuid='{$uuid}' AND type='0'",
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
	 * 获得所有祝炮
	 *
	 * @param
	 * @return array 祝炮
	 */
	public function getAllHello()
	{
		$getAllHello = $this->_uCache->getData('hello');

		return $getAllHello;
	}

	/**
	 * 获得所有消息
	 *
	 * @param
	 * @return array 消息
	 */
	public function getAllMessage()
	{
		$getAllMessage = $this->_uCache->getData('message');

		return $getAllMessage;
	}

	/**
	 * 发送消息
	 *
	 * @param array $param 参数列表
	 */
	public function addMessage( $param )
	{
		if( ! $param['uuid'])
		{
			$param['uuid'] = $this->uuid;
		}
		
		$this->_udb->addData('uuid', $param['uuid']);
		$this->_udb->addData('fid', $param['fid']);
		$this->_udb->addData('content', $param['content']);
		$this->_udb->addData('addtime', time());
		$this->_udb->addData('type', $param['type']);
		
		$this->_udb->dataInsert('user_message');
		$this->_uCache->flush('hello');
		$this->_uCache->flush('message');
	}
}

/* End of file Message.php */
/* Location: /class/models/Message.php */