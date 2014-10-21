<?php
/**
* @description	GM工具公告models
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/21
* @modifyTime
				2012/03/21	文件创建	朱勇
*/

class Inform {
	/**
	* 数据库对象
	*/
	private $_db = NULL;

	/**
	* 缓存对象
	*/
	private $_cache = NULL;

	/**
	* 管理员表名
	*/
	private $_tablename = 'inform';

	/**
	* 公告ID
	*/
	public $inform_id = 0;

	/**
	* 公告标题
	*/
	public $title = '';

	/**
	* 公告内容
	*/
	public $content = '';

	/**
	* 操作时间
	*/
	public $addtime = '';

	/**
	* 公告状态
	*/
	public $inform_status = 0;
	
	/**
	 * 构造函数
	 *
	 * @param
	 * @return
	 */
	public function __construct()
	{
		$this->_db = stra::db('gm');
		$this->_cache = stra::createCache('gm');

		$cacheVars = array
		(
			'inform' => array
			(
				'selectFrom' => 'inform WHERE inform_status="0" ORDER BY addtime ASC',
				'first' => FALSE,
				'lifeTime' => 60*60*24,
			),
		);

		while ( list ( $key, $item ) = @each ( $cacheVars ) )
		{
			$this->_cache->addData( $key, $item );
		}
	}

	/**
	 * 获取公告ID
	 *
	 * @param
	 * @return int 公告ID
	 */
	public function getInformId()
	{
		return $this->inform_id;
	}

	/**
	 * 设置公告ID
	 *
	 * @param int $inform_id 公告ID
	 * @return
	 */
	public function setInformId($inform_id)
	{
		$this->inform_id = $inform_id;
	}

	/**
	 * 获取公告标题
	 *
	 * @param
	 * @return string 公告标题
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * 设置公告标题
	 *
	 * @param string $title 公告标题
	 * @return
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * 获取公告内容
	 *
	 * @param
	 * @return string 公告内容
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * 设置公告内容
	 *
	 * @param string $content 公告内容
	 * @return
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}

	/**
	 * 获取操作时间
	 *
	 * @param
	 * @return int 操作时间
	 */
	public function getAddtime()
	{
		return $this->addtime;
	}

	/**
	 * 设置操作时间
	 *
	 * @param string $addtime 操作时间
	 * @return
	 */
	public function setAddtime($addtime)
	{
		$this->addtime = $addtime;
	}

	/**
	 * 获取公告状态
	 *
	 * @param
	 * @return int 公告状态
	 */
	public function getInformStatus()
	{
		return $this->inform_status;
	}

	/**
	 * 设置公告状态
	 *
	 * @param int $inform_status 公告状态
	 * @return
	 */
	public function setInformStatus($inform_status)
	{
		$this->inform_status = $inform_status;
	}

	/**
	 * 获得所有公告信息
	 *
	 * @param
	 * @return array 所有公告信息
	 */
	public function getInformAll()
	{
		$getInformAll = $this->_db->getRows("SELECT inform_id,title,content,addtime,inform_status FROM {$this->_tablename} WHERE inform_status='0' ORDER BY addtime ASC");

		return $getInformAll;
	}

	/**
	 * 获得所有公告信息（缓存）
	 *
	 * @param
	 * @return array 所有公告信息
	 */
	public function getInformAllCache()
	{
		$getInformAllCache = $this->_cache->getData('inform');

		return $getInformAllCache;
	}

	/**
	 * 根据公告ID获得公告信息
	 *
	 * @param int $inform_id 公告ID
	 * @return bool 获取结果
	 */
	public function getInformById($inform_id)
	{
		$getInformById = $this->_db->getRow("SELECT inform_id,title,content,addtime,inform_status FROM {$this->_tablename} WHERE inform_id='{$inform_id}' AND inform_status='0'");

		if($getInformById && is_array($getInformById))
		{
			$this->setInformId($getInformById['inform_id']);
			$this->setTitle($getInformById['title']);
			$this->setContent($getInformById['content']);
			$this->setAddtime($getInformById['addtime']);
			$this->setInformStatus($getInformById['inform_status']);

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * 新增公告信息
	 *
	 * @param
	 * @return int 自增量ID
	 */
	public function addInform()
	{
		$title = $this->getTitle();
		$content = $this->getContent();
		$addtime = $this->getAddtime();
		$inform_status = $this->getInformStatus();
		
		$this->_db->addData('title', $title);
		$this->_db->addData('content', $content);
		$this->_db->addData('addtime', $addtime);
		$this->_db->addData('inform_status', $inform_status);
		
		$this->_db->dataInsert($this->_tablename);
		
		return $this->_db->lastInsertId();
	}

	/**
	 * 设置公告信息
	 *
	 * @param
	 * @return bool 执行结果
	 */
	public function setInform()
	{
		$inform_id = $this->getInformId();
		$title = $this->getTitle();
		$content = $this->getContent();
		$addtime = $this->getAddtime();
		$inform_status = $this->getInformStatus();
		
		$this->_db->addData('title', $title);
		$this->_db->addData('content', $content);
		$this->_db->addData('addtime', $addtime);
		$this->_db->addData('inform_status', $inform_status);
		
		$result = $this->_db->dataUpdate($this->_tablename, "inform_id='{$inform_id}'");
		
		return $result;
	}
}

/* End of file Inform.php */
/* Location: /gm/models/Inform.php */