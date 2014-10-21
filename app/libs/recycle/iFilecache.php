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

Class iFilecache
{

	#============================================================================
	# 公共属性: 服务器参数 这里是默认设置，具体请在base.setup.php中设置
	#----------------------------------------------------------------------------
	Var $localCacheFile = "sysdata/cache/";
	Var $cacheArray     = Array();	//配置表 ？？？ 有什么作用
	Var $mem_cache_flag   = false;  //设置是否加栽memcache缓存 true为加载 false不加载
	var $expire_time =3600;
	var $path; //需要缓存的文件路径信息
	var $userInfo;

	#============================================================================
	# 申明
	#----------------------------------------------------------------------------
	Function ClassCacheCenter( $path='')
	{
		global $userInfo;
		$this->userInfo = $userInfo;
		if($this->mem_cache_flag)  //启用memcache.php　缓存
		{
			include_once ('memcache.php');
			$this->mem = new MEM_CACHE;
			$channel_name = "tiantangwan";
			$this->mem->set_channel($channel_name);
			$ident = time().$this->getRand();
			$this->mem->set_ident($ident);
		}

		if ( !$this->path ){
			$this->path =$this->getCurrentFileAndParams();
		}

	}
	function setExpireTime( $time_long ){
		$this->expire_time = $time_long;
	}
	#============================================================================
	# 获得登陆随机码 第 3 6 位 数字
	#----------------------------------------------------------------------------
	Function getRand () {
		$bit = 3;
		for ($i = 1; $i <= $bit; $i++) {
			$chrNum .= mt_rand();
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
	function isCacheFile($cachePath ,$time='0')
	{
		$dtime = time();
		if ( !$time ){
			$time= $this->expire_time;
		}
		return ($dtime - filemtime($cachePath) <= $time);
	}

	/**
 * 获取cache文件中的 获取缓存文件 输入文件地址
 *
 * @param unknown_type $this->path
 * @return unknown
 */
	Function getbody($path)
	{
		$s	= '';
		$f	= @fopen($path, "r");
		@flock($fd, LOCK_UN);
		if ($f)
		{
			while (!feof($f))
			{
				$s .= fgets($f, 1024);
			}
			fclose($f);
		}
		else
		{
			return false;
		}
		return $s;
	}


	/**
	 * 保存临时cache 写入缓存文件 
	 *
	 * @param unknown_type $myfile  文件名
	 * @param unknown_type $mybody  文件内容
	 * @return unknown 不返回东西
	 */
	Function makeFile($myfile,$mybody)
	{
		$fd = @fopen($myfile, "w");
		@flock($fd, LOCK_EX);
		if ($fd)
		{
			if (fwrite($fd, $mybody))
			{
				fclose($fd);
				return true;
			}
			else
			{
				fclose($fd);
			}
		}
		return false;
	}


	/**
	 * 公共方法: 关闭连接 
	 * 关闭memcache 缓存
	 */

	Function close()
	{
		if($this->mem_cache_flag)
		{
			$this->mem->close();
		}

	}

	/**
	 * 写缓存日志
	 *
	 * @param unknown_type $s 缓存内容
	 */
	function putCacheLog($s)
	{
		$time = time();
		$logname = date("Ymd", $time);
		$f = fopen("sysdata/cache/log/".$logname.".log", "a+");

		if ($f)
		{
			$s .= " ".date("Y-m-d H:i:s", $time)."\n";
			fwrite($f, $s);
			fclose($f);
		}
	}


	/**
	 * 读取CACHE
	 *
	 * @param unknown_type $this->path  文件路径
	 * @param unknown_type $time  过期时间
	 * @return unknown
	 */
	Function cacheRead()
	{


		if( empty($this->path))
		return false;
		//		if(empty($time) || $time<=0)

		$pkey = md5($this->path); //生成缓存文件名

		$p = substr($pkey,0,2);
		$f = "{$pkey}_{$this->userInfo['user_id']}.html"; //完整的文件名

		$LocalCacheFilePath = $this->localCacheFile.$p.'/'.$f; //完整的文件缓存路径
		if(is_file($LocalCacheFilePath) && $this->isCacheFile($LocalCacheFilePath))
		{
			if( $this->mem_cache_flag) //从memcache读取缓存内容文件的时间
			{
				$memcachecontent = $this->mem->get($pkey);  //从memcache读取文件缓存时间
			}

			//				$filemtime = filemtime($LocalCacheFilePath);  //读取本地上次缓存文件的时间


			if( ( empty($memcachecontent) || $memcachecontent > $filemtime ) && $this->mem_cache_flag)
			{

				if($_GET['test'])
				{
					$this->putCacheLog($pkey.'_read false');
				}
				return false;
			}
			else
			{

				if($_GET['test'])
				{
					$this->putCacheLog($pkey.'_read true');
				}

				$rd = $this->getbody($LocalCacheFilePath);  //获取本地文件缓存
				return $rd;
			}
		}
		else
		{
			return false;
		}

	}

	function display( ){
		if ( $content =  $this->cacheRead() )
		{
			echo $content;
			exit();
		} else {
			return false;
		}
	}
	function getCurrentFileAndParams(  ){
		$param = current($_SERVER['argv']);
		$this->path = "{$_SERVER['PHP_SELF']}?{$param}";
		return $this->path;
	}
	function setPath( $path ){
		if ( !$path ) return false;
		$this->path = $path;
	}
	#============================================================================
	# 删除CACHE
	#----------------------------------------------------------------------------
	Function cacheDelete()
	{
		if( empty($this->path))	return false;

		$pkey = md5($this->path);
		$this->mem->delete($pkey);
		return true;

	}


	/**
	 *  写入CACHE
	 *
	 * @param unknown_type $this->path 缓存文件的路径信息可以是当前文件路径 
	 * @param unknown_type $content //缓存内容
	 * @param unknown_type $tag //关键识别标记
	 * @param unknown_type $time //缓存生命周期
	 * @return unknown
	 */
	Function cacheWrite($content,$tag='',$time="3600")
	{

		if(  empty($this->path) || empty($content) ){
			return false;
		}

		clearstatcache(); //清除文件状态缓存 php自有方法

		$pkey = md5($this->path); //生成缓存文件名 且用来做memcache的主键

		$p = substr($pkey,0,2); //取前两个字符为目录名
		$f = "{$pkey}_{$this->userInfo['user_id']}.html";

		if(empty($time))
		$time=3600; //默认缓存时间1个小时

		$tmpCacheFileName = $this->localCacheFile.$p.'/'.$f; //缓存文件路径

		if (!file_exists($this->localCacheFile.$p))
		@mkdir($this->localCacheFile.$p,0777); //尝试新建一个由 pathname 指定的目录。

		$this->makeFile($tmpCacheFileName,$content); //写入文件和目录

		if($_GET['test'])
		{
			$this->putCacheLog($pkey.'_write');
		}

		if(!$this->mem_cache_flag) return true;
		$memcachecontentget = $this->mem->get($pkey);

		if(!$memcachecontentget) //把该文件缓存信息写入memcache
		{
			$this->mem->delete($pkey);
			$resultlist = $this->mem->set($pkey,time(),$time,MEMCACHE_COMPRESSED);
		}

		return true;



	}
}



?>
