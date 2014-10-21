<?php
/**
* @description	抽奖记录models
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/23
* @modifyTime
				2012/03/23	文件创建	朱勇
*/

class Lotto extends Init {
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

		$addtime = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

		$cacheVars = array (
			'lotto' => array (
				'selectFrom' => "user_lotto WHERE uuid='{$uuid}' AND addtime>'{$addtime}' ORDER BY type ASC",
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
	 * 获得当天的所有摇奖记录
	 *
	 * @param
	 * @return array 所有摇奖记录
	 */
	public function getLottoAll()
	{
		$getLottoAll = $this->_uCache->getData('lotto');

		return $getLottoAll;
	}

	/**
	 * 新建摇奖记录
	 * @param array $param 参数列表
	 */
	public function newLotto( $param )
	{
		if( ! $param['uuid'])
		{
			$param['uuid'] = $this->uuid;
		}
		
		$this->_udb->addData('uuid', $param['uuid']);
		$this->_udb->addData('type', $param['type']);
		$this->_udb->addData('sid', $param['sid']);
		$this->_udb->addData('addtime', time());
		
		$this->_udb->dataInsert('user_lotto');
		$this->_uCache->flush('lotto');
	}

	/**
	 * 获取玩家摇奖记录
	 * @param int $uuid 玩家ID
	 */
	function getLottoType($uuid)
	{
		$lotto_type = array();
		$lotto_all = $this->getLottoAll();
		if($lotto_all && is_array($lotto_all))
		{
			foreach($lotto_all as $lotto_all_value)
			{
				$lotto_type[$lotto_all_value['type']] = $lotto_all_value['addtime'];
			}
			unset($lotto_all_value);
		}

		return $lotto_type;
	}
}

/* End of file Lotto.php */
/* Location: /class/models/Lotto.php */