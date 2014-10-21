<?php
/**
* @description	GM工具模块models
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/09
* @modifyTime
				2012/03/09	文件创建	朱勇
*/

class Module {
	/**
	* 数据库对象
	*/
	private $_db = NULL;

	/**
	* 管理员表名
	*/
	private $_tablename = 'module';

	/**
	* 模块ID
	*/
	public $module_id = 0;

	/**
	* 模块名称
	*/
	public $module_name = 0;

	/**
	* 功能模块URL
	*/
	public $module_url = 0;

	/**
	* 模块等级
	*/
	public $module_grade = 0;

	/**
	* 父级模块ID
	*/
	public $parent_id = 0;

	/**
	* 模块状态
	*/
	public $module_status = 0;

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
	 * 获取模块ID
	 *
	 * @param
	 * @return int 模块ID
	 */
	public function getModuleId()
	{
		return $this->module_id;
	}

	/**
	 * 设置模块ID
	 *
	 * @param int $module_id 模块ID
	 * @return
	 */
	public function setModuleId($module_id)
	{
		$this->module_id = $module_id;
	}

	/**
	 * 获取模块名称
	 *
	 * @param
	 * @return string 模块名称
	 */
	public function getModuleName()
	{
		return $this->module_name;
	}

	/**
	 * 设置模块名称
	 *
	 * @param string $module_name 模块名称
	 * @return
	 */
	public function setModuleName($module_name)
	{
		$this->module_name = $module_name;
	}

	/**
	 * 获取功能模块URL
	 *
	 * @param
	 * @return string 功能模块URL
	 */
	public function getModuleUrl()
	{
		return $this->module_url;
	}

	/**
	 * 设置功能模块URL
	 *
	 * @param string $module_url 功能模块URL
	 * @return
	 */
	public function setModuleUrl($module_url)
	{
		$this->module_url = $module_url;
	}

	/**
	 * 获取模块等级
	 *
	 * @param
	 * @return int 模块等级
	 */
	public function getModuleGrade()
	{
		return $this->module_grade;
	}

	/**
	 * 设置模块等级
	 *
	 * @param int $module_grade 模块等级
	 * @return
	 */
	public function setModuleGrade($module_grade)
	{
		$this->module_grade = $module_grade;
	}

	/**
	 * 获取父级模块ID
	 *
	 * @param
	 * @return int 父级模块ID
	 */
	public function getParentId()
	{
		return $this->parent_id;
	}

	/**
	 * 设置父级模块ID
	 *
	 * @param int $parent_id 父级模块ID
	 * @return
	 */
	public function setParentId($parent_id)
	{
		$this->parent_id = $parent_id;
	}

	/**
	 * 获取模块状态
	 *
	 * @param
	 * @return int 模块状态
	 */
	public function getModuleStatus()
	{
		return $this->module_status;
	}

	/**
	 * 设置模块状态
	 *
	 * @param int $module_status 模块状态
	 * @return
	 */
	public function setModuleStatus($module_status)
	{
		$this->module_status = $module_status;
	}

	/**
	 * 获得所有模块信息
	 *
	 * @param
	 * @return array 所有模块信息
	 */
	public function getModuleAll()
	{
		$getModuleAll = $this->_db->getRows("SELECT module_id,module_name,module_url,module_grade,parent_id FROM {$this->_tablename} WHERE module_status='0'");

		return $getModuleAll;
	}

	/**
	 * 根据模块ID获得模块信息
	 *
	 * @param int $module_id 模块ID
	 * @return bool 获取结果
	 */
	public function getModuleById($module_id)
	{
		$getModuleById = $this->_db->getRow("SELECT module_id,module_name,module_url,module_grade,parent_id,module_status FROM {$this->_tablename} WHERE module_id='{$module_id}' AND module_status='0'");

		if($getModuleById && is_array($getModuleById))
		{
			$this->setModuleId($getModuleById['module_id']);
			$this->setModuleName($getModuleById['module_name']);
			$this->setModuleUrl($getModuleById['module_url']);
			$this->setModuleGrade($getModuleById['module_grade']);
			$this->setParentId($getModuleById['parent_id']);
			$this->setModuleStatus($getModuleById['module_status']);

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * 根据父级模块ID获得模块信息
	 *
	 * @param int $parent_id 父级模块ID
	 * @return array 模块信息
	 */
	public function getModuleByParentId($parent_id)
	{
		$getModuleByParentId = $this->_db->getRows("SELECT module_id,module_name,module_url,module_grade,parent_id FROM {$this->_tablename} WHERE parent_id='{$parent_id}' AND module_status='0'");

		return $getModuleByParentId;
	}

	/**
	 * 新增模块信息
	 *
	 * @param
	 * @return int 自增量ID
	 */
	public function addModule()
	{
		$module_name = $this->getModuleName();
		$module_url = $this->getModuleUrl();
		$module_grade = $this->getModuleGrade();
		$parent_id = $this->getParentId();
		$module_status = $this->getModuleStatus();
		
		$this->_db->addData('module_name', $module_name);
		$this->_db->addData('module_url', $module_url);
		$this->_db->addData('module_grade', $module_grade);
		$this->_db->addData('parent_id', $parent_id);
		$this->_db->addData('module_status', $module_status);
		
		$this->_db->dataInsert($this->_tablename);
		
		return $this->_db->lastInsertId();
	}

	/**
	 * 设置模块信息
	 *
	 * @param
	 * @return bool 执行结果
	 */
	public function setModule()
	{
		$module_id = $this->getModuleId();
		$module_name = $this->getModuleName();
		$module_url = $this->getModuleUrl();
		$module_grade = $this->getModuleGrade();
		$parent_id = $this->getParentId();
		$module_status = $this->getModuleStatus();
		
		$this->_db->addData('module_name', $module_name);
		$this->_db->addData('module_url', $module_url);
		$this->_db->addData('module_grade', $module_grade);
		$this->_db->addData('parent_id', $parent_id);
		$this->_db->addData('module_status', $module_status);
		
		$result = $this->_db->dataUpdate($this->_tablename, "module_id='{$module_id}'");
		
		return $result;
	}
}

/* End of file Module.php */
/* Location: /gm/models/Module.php */