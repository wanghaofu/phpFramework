<?php
/**
* @description	GM工具杂志models
* @author		朱勇 <ZhuYong@ultizen.com>
* @creatTime	2012/03/21
* @modifyTime
				2012/03/21	文件创建	朱勇
*/

class Event {
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
	private $_tablename = 'event';

	/**
	* 杂志ID
	*/
	public $event_id = 0;

	/**
	* 杂志标题
	*/
	public $title = '';

	/**
	* 杂志内容
	*/
	public $content = '';

	/**
	* 操作时间
	*/
	public $addtime = '';

	/**
	* 杂志状态
	*/
	public $event_status = 0;
	
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
			'event' => array
			(
				'selectFrom' => 'event WHERE event_status="0" ORDER BY addtime ASC',
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
	 * 获取杂志ID
	 *
	 * @param
	 * @return int 杂志ID
	 */
	public function getEventId()
	{
		return $this->event_id;
	}

	/**
	 * 设置杂志ID
	 *
	 * @param int $event_id 杂志ID
	 * @return
	 */
	public function setEventId($event_id)
	{
		$this->event_id = $event_id;
	}

	/**
	 * 获取杂志标题
	 *
	 * @param
	 * @return string 杂志标题
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * 设置杂志标题
	 *
	 * @param string $title 杂志标题
	 * @return
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * 获取杂志内容
	 *
	 * @param
	 * @return string 杂志内容
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * 设置杂志内容
	 *
	 * @param string $content 杂志内容
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
	 * 获取杂志状态
	 *
	 * @param
	 * @return int 杂志状态
	 */
	public function getEventStatus()
	{
		return $this->event_status;
	}

	/**
	 * 设置杂志状态
	 *
	 * @param int $event_status 杂志状态
	 * @return
	 */
	public function setEventStatus($event_status)
	{
		$this->event_status = $event_status;
	}

	/**
	 * 获得所有杂志信息
	 *
	 * @param
	 * @return array 所有杂志信息
	 */
	public function getEventAll()
	{
		$getEventAll = $this->_db->getRows("SELECT event_id,title,content,addtime,event_status FROM {$this->_tablename} WHERE event_status='0' ORDER BY addtime ASC");

		return $getEventAll;
	}

	/**
	 * 获得所有杂志信息（缓存）
	 *
	 * @param
	 * @return array 所有杂志信息
	 */
	public function getEventAllCache()
	{
		$getEventAllCache = $this->_cache->getData('event');

		return $getEventAllCache;
	}

	/**
	 * 根据杂志ID获得杂志信息
	 *
	 * @param int $event_id 杂志ID
	 * @return bool 获取结果
	 */
	public function getEventById($event_id)
	{
		$getEventById = $this->_db->getRow("SELECT event_id,title,content,addtime,event_status FROM {$this->_tablename} WHERE event_id='{$event_id}' AND event_status='0'");

		if($getEventById && is_array($getEventById))
		{
			$this->setEventId($getEventById['event_id']);
			$this->setTitle($getEventById['title']);
			$this->setContent($getEventById['content']);
			$this->setAddtime($getEventById['addtime']);
			$this->setEventStatus($getEventById['event_status']);

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * 新增杂志信息
	 *
	 * @param
	 * @return int 自增量ID
	 */
	public function addEvent()
	{
		$title = $this->getTitle();
		$content = $this->getContent();
		$addtime = $this->getAddtime();
		$event_status = $this->getEventStatus();
		
		$this->_db->addData('title', $title);
		$this->_db->addData('content', $content);
		$this->_db->addData('addtime', $addtime);
		$this->_db->addData('event_status', $event_status);
		
		$this->_db->dataInsert($this->_tablename);
		
		return $this->_db->lastInsertId();
	}

	/**
	 * 设置杂志信息
	 *
	 * @param
	 * @return bool 执行结果
	 */
	public function setEvent()
	{
		$event_id = $this->getEventId();
		$title = $this->getTitle();
		$content = $this->getContent();
		$addtime = $this->getAddtime();
		$event_status = $this->getEventStatus();
		
		$this->_db->addData('title', $title);
		$this->_db->addData('content', $content);
		$this->_db->addData('addtime', $addtime);
		$this->_db->addData('event_status', $event_status);
		
		$result = $this->_db->dataUpdate($this->_tablename, "event_id='{$event_id}'");
		
		return $result;
	}
}

/* End of file Event.php */
/* Location: /gm/models/Event.php */