<?php
/**
* @description	GM工具用户模块models
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/09
* @modifyTime
				2012/03/09	文件创建	朱勇
*/

class Admin {
	/**
	* 数据库对象
	*/
	private $_db = NULL;

	/**
	* 管理员表名
	*/
	private $_tablename = 'admin_user';

	/**
	* 管理员ID
	*/
	public $admin_id = 0;

	/**
	* 管理员名称
	*/
	public $admin_name = '';

	/**
	* 管理员密码
	*/
	public $admin_pass = '';

	/**
	* 职位ID
	*/
	public $position_id = 0;

	/**
	* 管理员状态
	*/
	public $admin_status = 0;
	
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
	 * 获取管理员ID
	 *
	 * @param
	 * @return int 管理员ID
	 */
	public function getAdminId()
	{
		return $this->admin_id;
	}

	/**
	 * 设置管理员ID
	 *
	 * @param int $admin_id 管理员ID
	 * @return
	 */
	public function setAdminId($admin_id)
	{
		$this->admin_id = $admin_id;
	}

	/**
	 * 获取管理员名称
	 *
	 * @param
	 * @return string 管理员名称
	 */
	public function getAdminName()
	{
		return $this->admin_name;
	}

	/**
	 * 设置管理员名称
	 *
	 * @param string $admin_name 管理员名称
	 * @return
	 */
	public function setAdminName($admin_name)
	{
		$this->admin_name = $admin_name;
	}

	/**
	 * 获取管理员密码
	 *
	 * @param
	 * @return string 管理员密码
	 */
	public function getAdminPass()
	{
		return $this->admin_pass;
	}

	/**
	 * 设置管理员密码
	 *
	 * @param string $admin_pass 管理员密码
	 * @return
	 */
	public function setAdminPass($admin_pass)
	{
		$this->admin_pass = $admin_pass;
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
	 * 获取管理员状态
	 *
	 * @param
	 * @return int 管理员状态
	 */
	public function getAdminStatus()
	{
		return $this->admin_status;
	}

	/**
	 * 设置管理员状态
	 *
	 * @param int $admin_status 管理员状态
	 * @return
	 */
	public function setAdminStatus($admin_status)
	{
		$this->admin_status = $admin_status;
	}

	/**
	 * 根据管理员名称获得管理员信息
	 *
	 * @param string $admin_name 管理员名称
	 * @return bool 获取结果
	 */
	public function getAdminByName($admin_name)
	{
		$getAdminByName = $this->_db->getRow("SELECT admin_id,admin_name,admin_pass,position_id,admin_status FROM {$this->_tablename} WHERE admin_name='{$admin_name}' AND admin_status='0'");

		if($getAdminByName && is_array($getAdminByName))
		{
			$this->setAdminId($getAdminByName['admin_id']);
			$this->setAdminName($getAdminByName['admin_name']);
			$this->setAdminPass($getAdminByName['admin_pass']);
			$this->setPositionId($getAdminByName['position_id']);
			$this->setAdminStatus($getAdminByName['admin_status']);

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * 获得所有用户信息
	 *
	 * @param
	 * @return array 所有用户信息
	 */
	public function getAdminAll()
	{
		$getAdminAll = $this->_db->getRows("SELECT admin_id,admin_name,admin_pass,position_id,admin_status FROM {$this->_tablename} WHERE admin_status='0'");

		return $getAdminAll;
	}

	/**
	 * 根据用户ID获得用户信息
	 *
	 * @param int $admin_id 用户ID
	 * @return bool 获取结果
	 */
	public function getAdminById($admin_id)
	{
		$getAdminById = $this->_db->getRow("SELECT admin_id,admin_name,admin_pass,position_id,admin_status FROM {$this->_tablename} WHERE admin_id='{$admin_id}' AND admin_status='0'");

		if($getAdminById && is_array($getAdminById))
		{
			$this->setAdminId($getAdminById['admin_id']);
			$this->setAdminName($getAdminById['admin_name']);
			$this->setAdminPass($getAdminById['admin_pass']);
			$this->setPositionId($getAdminById['position_id']);
			$this->setAdminStatus($getAdminById['admin_status']);

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * 新增用户信息
	 *
	 * @param
	 * @return int 自增量ID
	 */
	public function addAdmin()
	{
		$admin_name = $this->getAdminName();
		$admin_pass = $this->getAdminPass();
		$position_id = $this->getPositionId();
		$admin_status = $this->getAdminStatus();
		
		$this->_db->addData('admin_name', $admin_name);
		$this->_db->addData('admin_pass', $admin_pass);
		$this->_db->addData('position_id', $position_id);
		$this->_db->addData('admin_status', $admin_status);
		
		$this->_db->dataInsert($this->_tablename);
		
		return $this->_db->lastInsertId();
	}

	/**
	 * 设置用户信息
	 *
	 * @param
	 * @return bool 执行结果
	 */
	public function setAdmin()
	{
		$admin_id = $this->getAdminId();
		$admin_name = $this->getAdminName();
		$admin_pass = $this->getAdminPass();
		$position_id = $this->getPositionId();
		$admin_status = $this->getAdminStatus();
		
		$this->_db->addData('admin_name', $admin_name);
		$this->_db->addData('admin_pass', $admin_pass);
		$this->_db->addData('position_id', $position_id);
		$this->_db->addData('admin_status', $admin_status);
		
		$result = $this->_db->dataUpdate($this->_tablename, "admin_id='{$admin_id}'");
		
		return $result;
	}

	/**
	 * 用户密码md5加密算法
	 *
	 * @param string $admin_pass 原始密码
	 * @return string 原始密码
	 */
	public function PassMd5($admin_pass)
	{
		$admin_pass_md5 = md5($admin_pass);
		$admin_pass_md5 = md5('st'.$admin_pass_md5.'ra');
		$admin_pass_md5 = md5('spe'.$admin_pass_md5.'ed');

		return $admin_pass_md5;
	}
}

/* End of file Admin.php */
/* Location: /gm/models/Admin.php */