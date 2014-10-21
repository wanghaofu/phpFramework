<?php
/*************************************************
* memcache类
* 编写:wangtao
* 日期: 2007-9-13
**************************************************/
error_reporting(0);
class MEM_CACHE {
		
	var $x_connect_info	= array();	// 当前的链接信息
	var $x_link;				// 当前的MEMCACHE链接
	var $x_bug;				// 调试选项
	var $x_iserror;				// 本次操作是否成功
	var $x_err_num;				// 错误编号
	var $x_connect_all	= array();	// 所有的MEMCACHE配置信息	
	var $x_channel;				// 频道标识
	var $x_ident;				// 本次链接唯一标识，如用户名等
	var $x_connect_p;			// 长链接标识
	
	function MEM_CACHE()
	{
		$this->x_link = false;
		$this->x_bug = false;
		$this->x_iserror = false;
		$this->x_err_num=0;
		$this->x_connect_p=0;		
	}
	
	function set_channel($channel)
	{
		$this->x_channel = $channel;		
	}
	
	function set_ident($ident)
	{
		$this->x_ident = $ident;
		$this->x_link = false;
		$this->x_iserror = false;
		$this->x_err_num=0;
	}
	
	function set_connect_attr($value)
	{
		$this->x_connect_p = $value;
	}
	
	function set_debug($flag)
	{
		$this->x_bug = $flag;
	}
	
	function is_ok()
	{
		return !$this->x_iserror;
	}
	
	function get_connect_info()
	{
		return $this->x_connect_info;
	}
	
	//--打开数据库链接--
	function openconnect()
	{
		if($this->x_link)
			return $this->x_link;
			
		if($this->x_channel=='' || empty($this->x_channel))
		{
			return $this->_set_err(101);
		}
		
		if($this->x_ident=='' || empty($this->x_ident))
		{
			return $this->_set_err(102);
		}
		
		$this->x_connect_all[0] = array("h" => "192.168.0.190","p" => 11911);
		
		//echo md5($this->x_ident)."\n";
		$tag = substr(md5($this->x_ident),0,2);
		//echo (int)(hexdec($tag)/64);
		$this->x_connect_info = $this->x_connect_all[0];
		
		if ($this->x_bug)
		{
			foreach ($this->x_connect_info as $v)
			{
				echo "MEMCADHE: $v\n";
			}
		}
		
		
		if($this->x_connect_p == 0)
		{
			$this->x_link = memcache_connect($this->x_connect_info['h'], $this->x_connect_info['p']);
		}
		else
		{
			$this->x_link = memcache_pconnect($this->x_connect_info['h'], $this->x_connect_info['p']);
		}

		if (!$this->x_link)
			return $this->_set_err(103);
		
		return true;
	}
	
	function _set_err($e)
	{
		//echo "<br>".$e."<br>";
		$this->x_err_num = $e;
		$this->x_iserror = true;
		return false;
	}
	
	function get_err_msg()
	{
		$c = $this->x_err_num;
		switch ($c)
		{
		case 101:
			return $c . ': 频道标识不可为空';
		case 102:
			return $c . ': 本次链接唯一标识不可为空。请调用set_ident($ident)函数进行设置';
		case 103:
			return $c . ': MEMCACHE服务器链接失败!请调用get_connect_info查看链接参数！';
		}
		return '';
	}

	function get($key)
	{
		if(!$this->openconnect())
			return false;
			
		$key = $this->channel.$this->ident.$key;
		$value = memcache_get($this->x_link, $key);
		return $value;
	}

	function set($key, $value, $expire = 0, $flag = MEMCACHE_COMPRESSED)
	{
		if(!$this->openconnect())
			return false;
			
		$key = $this->channel.$this->ident.$key;

		if($expire == 0)
		{
			return memcache_set($this->x_link, $key, $value, $flag);
		}
		else
		{
			return memcache_set($this->x_link, $key, $value, $flag, $expire);
		}
	}

	function replace($key, $value, $expire = 0,$flag = MEMCACHE_COMPRESSED)
	{
		if(!$this->openconnect())
			return false;
			
		$key = $this->channel.$this->ident.$key;
		
		if($expire == 0)
		{
			return memcache_replace($this->x_link, $key, $value, $flag);
		}
		else
		{
			return memcache_replace($this->x_link, $key, $value, $flag, $expire);
		}
	}

	function delete($key)
	{
		if(!$this->openconnect())
			return false;
			
		$key = $this->channel.$this->ident.$key;
		
		return memcache_delete($this->x_link, $key);
	}

	//--------清除该SERVER上的所有值，此函数非常重要，请慎用！---------//
	function flush()
	{
		if(!$this->openconnect())
			return false;
		
		return memcache_flush($this->x_link);
	}

	function getstats()
	{
		if(!$this->openconnect())
			return false;
			
		if($this->x_bug == 0)
		{
			return memcache_get_stats($this->x_link);
		}
		else
		{
			$stats_array = memcache_get_stats($this->x_link);
			echo "<table border=\"0\" bgcolor=\"#000000\" cellspacing=\"1\">
					<tr bgcolor=\"#F8F8F8\">
						<td>
							Key
						</td>
						<td>
							Value
						</td>
					</tr>
				";
			foreach($stats_array as $k => $v)
			{
				echo "<tr bgcolor=\"#FFFFFF\">
						<td>
						{$k}
						</td>
						<td>
						{$v}
						</td>
					</tr>
					";
			}
			echo "</table>";
		}
	}

	function close()
	{
		if(!$this->openconnect())
			return false;
		
		return memcache_close($this->x_link);
	}
}

/*
// 创建一个MEM对象(MD5方式)
$mem = new MEM_CACHE;

// 设置频道标识_
$mem->set_channel("50zq");

//设置本次链接唯一标识，如用户名等。
$mem->set_ident($username);

// 设置一个KEY名字为nickname，值为e2000 有效期为120秒 并且压缩数据
$mem->set("nickname","e2000",120,MEMCACHE_COMPRESSED);

// 设置一个KEY名字为age，值为20 并且不压缩数据    永久保存
//$mem->set("age",20);

// 取得key的值
echo $mem->get("nickname");

// 删除key
$mem->delete("nickname");

// 取得服务器状态，并以表格形式打印
$mem->getstats();

// 设置系统为调制状态
$mem->set_debug(true);

// 清空所有key
$mem->flush();

// 关闭连接
$mem->close();
*/
?>
