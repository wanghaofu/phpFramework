<?
#==============================================================================================
# CACHE中心-统一处理模块 终端缓存
#Version 2007-7-19 v1.0 beta, Power by runtian
#Contact to wangruntian<wanghaofu@163.com>
#　
#----------------------------------------------------------------------------------------------
/**
 * 引入文件 当前目录．/memcache.php　缓存系统
 *　
 */

class iMemcachePath {
	
	#============================================================================
	# 公共属性: 服务器参数 这里是默认设置，具体请在base.setup.php中设置
	#----------------------------------------------------------------------------
	var $expire_time = 43200;
	var $pathInfo = array (); //需要缓存的文件路径信息
	var $userInfo;
	var $in;
	var $mem;
	var $tag;
	#============================================================================
	# 申明
	#----------------------------------------------------------------------------
	Function ClassMemcCacheCenter($path = '') {
		global $userInfo, $IN;
		
		$this->in = $IN;
		$this->userInfo = $userInfo;
		
		include_once ('./include/class/memcache.php');
		
		$this->mem = new Memcache ();
		$this->mem->connect ( "localhost", 11211 );
		
		if (! $this->pathInfo) {
			$this->pathInfo = $this->getCurrentFileAndParams ();
		}
	
	}
	function setExpireTime($time_long) {
		$this->expire_time = $time_long;
	}
	#============================================================================
	# 获得登陆随机码 第 3 6 位 数字
	#----------------------------------------------------------------------------
	Function getRand() {
		$bit = 3;
		for($i = 1; $i <= $bit; $i ++) {
			$chrNum .= mt_rand ();
		}
		Return $chrNum;
	}
	
	/**
	 * 本地cache有效时间 检测缓存是否过期
	 *
	 * @param unknown_type $path
	 * @param unknown_type $time
	 * @return unknown 如果没有过期返回真 否则返回假
	 */
	function isCacheFile($cachePath, $time = '0') {
		$dtime = time ();
		if (! $time) {
			$time = $this->expire_time;
		}
		return ($dtime - filemtime ( $cachePath ) <= $time);
	}
	
	/**
	 * 公共方法: 关闭连接 
	 * 关闭memcache 缓存
	 */
	
	public Function close() {
		if ($this->mem_cache_flag) {
			$this->mem->close ();
		}
	
	}
	
	/**
	 * 读取CACHE
	 *
	 * @param unknown_type $this->pathInfo  文件路径
	 * @param unknown_type $time  过期时间
	 * @return unknown
	 * 
	 * keystrunct =array( 'pathInfo' =>array(act) =>array(parmars));
	 * 路径信息 然后是动作信息 然后是参数信息
	 */
	public Function cacheRead() {
		if (empty ( $this->pathInfo ))
			return false;
		$cacheContent = $this->mem->get ( $this->pathInfo ['path'] );
		//		de($cacheContent,__file__,__line__,0,0);
		

		$content = $cacheContent [$this->pathInfo ['act']];
		if ($content && ! $content ['status']) {
			return $content [$this->pathInfo ['param']] ['content'];
		} else {
			return false;
		}
	
	}
	
	public function display() {
		if ($content = $this->cacheRead ()) {
			echo $content;
			exit ();
		} else {
			return false;
		}
	}
	private	function getCurrentFileAndParams() {
		$param = current ( $_SERVER ['argv'] );
		if (! $this->in ['act'])
			$this->in ['act'] = 'default';
		$pathInfo = array ('path' => $_SERVER ['PHP_SELF'], 'act' => $this->in ['act'], 'param' => $param );
		$this->pathInfo = $pathInfo;
		return $this->pathInfo;
	}
	function setPath($path) {
		if (! $path)
			return false;
		$this->pathInfo = $path;
	}
	#============================================================================
	# 删除CACHE
	#----------------------------------------------------------------------------
	public Function cacheDelete() {
		if (empty ( $this->pathInfo ))
			return false;
		
		$pkey = md5 ( $this->pathInfo );
		$this->mem->delete ( $pkey );
		return true;
	
	}
	
	/**
	 * 写入CACHE
	 *
	 * @param unknown_type $this->pathInfo 缓存文件的路径信息可以是当前文件路径 
	 * @param unknown_type $content //缓存内容
	 * @param unknown_type $tag //关键识别标记
	 * @param unknown_type $time //缓存生命周期
	 * @return unknown
	 */
	Function cacheWrite($content, $time = "3600") {
		
		if (empty ( $this->pathInfo ) || empty ( $content )) {
			return false;
		}
		$pkey = $this->pathInfo ['path']; //生成缓存文件名 且用来做memcache的主键
		

		$memcacheContentArr = $this->mem->get ( $pkey );
		if ($memcacheContentArr) {
			$this->deleteExpireData ( $memcacheContentArr ); //清理过期内容
		}
		$currentTime = time ();
		$memcacheContentArr [$this->pathInfo ['act']] ['status'] = 0;
		$memcacheContentArr [$this->pathInfo ['act']] [$this->pathInfo ['param']] = array ('content' => $content, 'status' => 0, 'expire_time' => $currentTime + $time );
		//		$f = "{$pkey}_{$this->userInfo['user_id']}.html";
		if (empty ( $time ))
			$time = 3600; //默认缓存时间1个小时
		$resultlist = $this->mem->set ( $pkey, $memcacheContentArr, MEMCACHE_COMPRESSED, $time );
		return true;
	}
	function deleteExpireData( $arr ) {
		$time = time();
		if (is_array ( $arr )) {
			foreach ( $arr as $key => $value ) {
				if ($value ['expire_time'] <= $time) {
					unset ( $value ['act'] ['param'] );
				}
			}
		}
	}
	
	function setStatus($act) {
		$pkey = $this->pathInfo ['path'];
		$memcacheContentArr = $this->mem->get ( $pkey );
		if ($memcacheContentArr) {
			$this->deleteExpireData ( $memcacheContentArr ); //清理过期内容
		}
		$currentTime = time ();
		$memcacheContentArr [$this->pathInfo ['act']] ['status'] = 1;
		$this->mem->set ( $pkey, $memcacheContentArr, MEMCACHE_COMPRESSED, $this->expire_time );
	}
}


class AutoDbMemcacheKey
{
	var $key;
	var $keyArr=array();
	
	var $where;  //顺序不确定
	var $tableName; //确定
	var $dbName; //确定
	function setKey($key)
	{
		
		
		
		
	}
	
	function setWhere()
	{
		
		$where = "{$user_id} =1";
		//设置
		//sheding yonghu  where
		$where['user_info'] = "user_id = {$this->user_id}";
		//shezhiyonghu daoju 
		
		$where['user_prop'] ="user_id = {$this->userId} && prop_id = 32";
		
		$where['user_prop_xx'] ="prop_id =21";
		
		$tableName = slg::user(32)->getTableName('xx');
//					slg::table::name();
		
		$key = md5($tableName.$where);
	}
	
	
	/**
	 * 
	 * class module
	 * {
	 * 	//where fenji 
	 * 	parent where 
	 *  where[xx1] = array(
	 *  	'xx = xx',
	 *  	'',  //父级为空
	 *  )
	 *  where[xx2] = array(
	 *  	'xx => xx',
	 *  	'xx1', //父级为xx1  增新该级的时候更新父级
	 *  	
	 *  
	 *  
	 * 
	 * 
	 * }
	 * 
	 * 
	 * how to use;
	 * 
	 * class iCacheDb
	 * {
	 * var $where;
	 * var $dbName;
	 * var $TableName;
	 * $db->setWhere(prop::prop_xx->where)
	 * $db->getRow($xx);
	 * 
	 * 
	 * function setWhere()
	 * {
	 * 	$this->where = null;
	 * }
	 * 
	 * function getRow()
	 * {
	 * }
	 * }
	 */
		
	
	
	
}

?>
