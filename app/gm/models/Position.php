<?php
/**
* @description	GM工具职位模块models
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/09
* @modifyTime
				2012/03/09	文件创建	朱勇
*/

class Position {
	/**
	* 数据库对象
	*/
	private $_db = NULL;

	/**
	* 管理员表名
	*/
	private $_tablename = 'position';

	/**
	* 职位ID
	*/
	public $position_id = 0;

	/**
	* 职位名称
	*/
	public $position_name = 0;

	/**
	* 功能模块列表
	*/
	public $module_list = 0;

	/**
	* 职位状态
	*/
	public $position_status = 0;

	/**
	 * 构造函数
	 *
	 * @param
	 * @return
	 */
	public function __construct()
	{
		$this->_db = stra::db('gm');
	}

	/**
	 * 获取职位ID
	 *
	 * @param
	 * @return int 职位ID
	 */
	public function getPositionId()
	{
		return $this->position_id;
	}

	/**
	 * 设置职位ID
	 *
	 * @param int $position_id 职位ID
	 * @return
	 */
	public function setPositionId($position_id)
	{
		$this->position_id = $position_id;
	}

	/**
	 * 获取职位名称
	 *
	 * @param
	 * @return string 职位名称
	 */
	public function getPositionName()
	{
		return $this->position_name;
	}

	/**
	 * 设置职位名称
	 *
	 * @param string $position_name 职位名称
	 * @return
	 */
	public function setPositionName($position_name)
	{
		$this->position_name = $position_name;
	}

	/**
	 * 获取功能模块列表
	 *
	 * @param
	 * @return string 功能模块列表
	 */
	public function getModuleList()
	{
		return $this->module_list;
	}

	/**
	 * 设置功能模块列表
	 *
	 * @param string $module_list 功能模块列表
	 * @return
	 */
	public function setModuleList($module_list)
	{
		$this->module_list = $module_list;
	}

	/**
	 * 获取职位状态
	 *
	 * @param
	 * @return int 职位状态
	 */
	public function getPositionStatus()
	{
		return $this->position_status;
	}

	/**
	 * 设置职位状态
	 *
	 * @param int $position_status 职位状态
	 * @return
	 */
	public function setPositionStatus($position_status)
	{
		$this->position_status = $position_status;
	}
	
	/**
	 * 获得所有职位信息
	 *
	 * @param
	 * @return array 所有职位信息
	 */
	public function getPositionAll()
	{
		$getPositionAll = $this->_db->getRows("SELECT position_id,position_name,module_list FROM {$this->_tablename} WHERE position_status='0'");

		return $getPositionAll;
	}

	/**
	 * 根据职位ID获得职位信息
	 *
	 * @param int $position_id 职位ID
	 * @return bool 获取结果
	 */
	public function getPositionById($position_id)
	{
		$getPositionById = $this->_db->getRow("SELECT position_id,position_name,module_list,position_status FROM {$this->_tablename} WHERE position_id='{$position_id}' AND position_status='0'");

		if($getPositionById && is_array($getPositionById))
		{
			$this->setPositionId($getPositionById['position_id']);
			$this->setPositionName($getPositionById['position_name']);
			$this->setModuleList($getPositionById['module_list']);
			$this->setPositionStatus($getPositionById['position_status']);

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * 新增职位信息
	 *
	 * @param
	 * @return int 自增量ID
	 */
	public function addPosition()
	{
		$position_name = $this->getPositionName();
		$module_list = $this->getModuleList();
		$position_status = $this->getPositionStatus();
		
		$this->_db->addData('position_name', $position_name);
		$this->_db->addData('module_list', $module_list);
		$this->_db->addData('position_status', $position_status);
		
		$this->_db->dataInsert($this->_tablename);
		
		return $this->_db->lastInsertId();
	}

	/**
	 * 设置职位信息
	 *
	 * @param
	 * @return bool 执行结果
	 */
	public function setPosition()
	{
		$position_id = $this->getPositionId();
		$position_name = $this->getPositionName();
		$module_list = $this->getModuleList();
		$position_status = $this->getPositionStatus();
		
		$this->_db->addData('position_name', $position_name);
		$this->_db->addData('module_list', $module_list);
		$this->_db->addData('position_status', $position_status);
		
		$result = $this->_db->dataUpdate($this->_tablename, "position_id='{$position_id}'");
		
		return $result;
	}
}

/* End of file Position.php */
/* Location: /gm/models/Position.php */