<?php
/**
* @description	背包models
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/29
* @modifyTime
				2012/03/29	文件创建	朱勇
*/

class Props extends Init {
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
			'props' => array (
				'selectFrom' => "user_props WHERE uuid='{$uuid}' AND num>0",
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
	 * 获得所有背包道具
	 *
	 * @param
	 * @return array 所有背包道具
	 */
	public function getPropsAll()
	{
		$getPropsAll = $this->_uCache->getData('props');

		return $getPropsAll;
	}

	/**
	 * 获得单个背包道具
	 *
	 * @param int $props_id 道具ID
	 * @return array 单个背包道具
	 */
	public function getProps($props_id)
	{
		$getPropsAll = $this->getPropsAll();
		if($getPropsAll && is_array($getPropsAll))
		{
			foreach($getPropsAll as $getPropsAll_value)
			{
				if($getPropsAll_value['props_id'] == $props_id)
				{
					return $getPropsAll_value;
				}
			}
			unset($getPropsAll_value);
		}

		return FALSE;
	}

	/**
	 * 新建背包道具
	 * @param array $param 参数列表
	 */
	public function newProps($param)
	{
		if( ! $param['uuid'])
		{
			$param['uuid'] = $this->uuid;
		}
		
		$this->_udb->addData('uuid', $param['uuid']);
		$this->_udb->addData('props_id', $param['props_id']);
		$this->_udb->addData('num', $param['num']);
		
		$this->_udb->dataInsert('user_props');
		$this->_uCache->flush('userprops');
	}

	/**
	 * 修改背包道具
	 * @param array $param 参数列表
	 */
	public function editProps($param, $isChange = FALSE)
	{
		if ( ! $param['uuid'])
		{
			$param['uuid'] = $this->uuid;
		}
		
		$this->_udb->addData('uuid', $param['uuid']);
		$this->_udb->addData('props_id', $param['props_id']);

		if( ! $isChange)
		{
			$this->_udb->set('num', $param['num']);
		} else {
			$this->_udb->set('num', 'num+'.$param['num'], FALSE);
		}
		
		$this->_udb->dataUpdate('user_props', "upid='".$param['upid']."'");
		$this->_uCache->flush('userprops');
	}

	/**
	 * 删除背包道具
	 * @param int $upid 背包道具ID
	 */
	public function delProps($upid)
	{
		$this->_udb->dataDel('user_props', 'upid', $upid);
		$this->_uCache->flush('userprops');
	}
}

/* End of file Props.php */
/* Location: /class/models/Props.php */