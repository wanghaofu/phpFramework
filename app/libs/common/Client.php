<?php
/******************************************************************

	Name: 客户端相关函数库
	Author: 王涛 ( tony )

 ******************************************************************/
class Client
{
	// URL 重定向
	public static function url_redirect($url = '', $client = false)
	{
		if ($url == '')
		{
			$url = $_SERVER['PHP_SELF'];
		}
		
		if (function_exists('db_close_all'))
			db_close_all();
		
		if ($client)
		{
			echo ("<script type='text/javascript'>window.location = '$url';</script>");
		}
		else
		{
			header("location:$url");
		}
		exit();
	}
	
	// 系统根 URL
	public static function url_root($url = '')
	{
		$scheme = '';
		if (!$url)
		{
			if (isset($_SERVER["HTTPS"]))
			{
				$scheme = $_SERVER["HTTPS"] == 'on' ? 'https://' : 'http://';
			}
			$urlRoot = $scheme . $_SERVER['HTTP_HOST'];
		}
		else
		{
			$parseUrl = parse_url($url);
			$urlRoot = $parseUrl['scheme'] . '://' . $parseUrl['host'];
		}
		return $urlRoot;
	}
	
	// 当前页面
	public static function url_current()
	{
		if (!$url = $_SERVER['REQUEST_URI'])
		{
			$url = $_SERVER['SCRIPT_NAME'];
			if ($_SERVER['QUERY_STRING'] != '')
			{
				$url .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
		$curUrl = self::url_root() . $url;
		return $curUrl;
	}
	
	// 来路页面
	public static function url_referer($default = '')
	{
		if (!isset($_SERVER['HTTP_REFERER']))
		{
			$_SERVER['HTTP_REFERER'] = '';
		}
		if (trim($_SERVER['HTTP_REFERER']) == '')
		{
			return $default;
		}
		else
		{
			return $_SERVER['HTTP_REFERER'];
		}
	}
	
	// 更早来路页面
	public static function url_last_referer($default = '')
	{
		if (trim($_POST['_REFERER']) == '')
		{
			return $default;
		}
		else
		{
			return $_POST['_REFERER'];
		}
	}
	
	// 获取客户端 IP
	public static function get_ip()
	{
		$ip = false;
		
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
		{
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$ips = explode(',', str_replace(' ', '', $_SERVER['HTTP_X_FORWARDED_FOR']));
			if ($ip)
			{
				array_unshift($ips, $ip);
				$ip = false;
			}
			
			for ($i = 0; $i < count($ips); $i++)
			{
				if (!eregi('^(10|172\.16|192\.168)\.', $ips[$i]))
				{
					$ip = $ips[$i];
					break;
				}
			}
		}
		return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
	}
	
	// IPv4 转数值
	public static function ipv4($strIPv4)
	{
		$parts = explode('.', $strIPv4);
		while (list($key, $item) = @each($parts))
		{
			$hex .= sprintf('%02s', dechex($item));
		}
		$ipValue = hexdec($hex);
		return $ipValue;
	}
	
	// IPv6 转数值 ( $splitNum = 分段数量, 返回一个包含 $splitNum 个数值的数组 )
	public static function ipv6($strIPv6, $splitNum = 2)
	{
		$parts = explode(':', $strIPv6);
		$splitIdx = count($parts) / $splitNum;
		while (list($key, $item) = @each($parts))
		{
			$hex .= sprintf('%04s', $item);
			if (($key + 1) % $splitIdx == 0)
			{
				$idx = intval($key / $splitIdx);
				$ipValue[$idx] = hexdec($hex);
				unset($hex);
			}
		}
		return strtoupper($ipValue);
	}
	
	// 数值转 IPv4
	public static function make_ipv4($ipValue)
	{
		$partNum = 4;
		$partLength = 2;
		$partSplit = '.';
		
		$ipParts = array();
		$ipString = sprintf('%0' . ($partNum * $partLength) . 's', dechex($ipValue));
		
		for ($i = 0; $i < $partNum; $i++)
			array_push($ipParts, hexdec(substr($ipString, $i * $partLength, $partLength)));
		$strIPv4 = join($partSplit, $ipParts);
		return $strIPv4;
	}
	
	// 数值转 IPv6
	public static function make_ipv6($ipValue)
	{
		$partNum = 8;
		$partLength = 4;
		$partSplit = ':';
		
		$ipParts = array();
		if (!is_array($ipValue))
			$ipValue = array(
				$ipValue
			);
		$partCount = count($ipValue);
		while (list($key, $item) = @each($ipValue))
			$ipString .= sprintf('%0' . ($partNum * $partLength / $partCount) . 's', strtoupper(dechex($item)));
		
		for ($i = 0; $i < $partNum; $i++)
			array_push($ipParts, substr($ipString, $i * $partLength, $partLength));
		$strIPv6 = join($partSplit, $ipParts);
		return $strIPv6;
	}
	
	// 分隔域名
	public static function split_domain($domain)
	{
		$arrayDomain = array();
		$parts = explode('.', $domain);
		$arrayDomain[0] = array_shift($parts);
		$arrayDomain[1] = join('.', $parts);
		return $arrayDomain;
	}
	
	// 获取 URL Query
	public static function get_query()
	{
		$queryString = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']) + 1, strlen($_SERVER['REQUEST_URI']));
		if ($length = strpos($queryString, '?'))
		{
			$queryString = substr($queryString, 0, $length);
		}
		return $queryString;
	}
	
	// 获取文件变量
	public static function get_argv()
	{
		$getVars = $_GET;
		$gArgv = array();
		if (isset($_SERVER['argv']))
		{
			$arrArgv = $_SERVER['argv'];
		}
		@array_shift($arrArgv);
		while (list($key, $item) = @each($arrArgv))
		{
			$argv = explode('=', $item);
			$gArgv[$argv[0]] = $argv[1];
		}
		$gArgv = array_merge($getVars, $gArgv);
		return $gArgv;
	}
	
	// UNIX/LINUX 下获取正在运行的进程 ID 数组
	public static function unix_get_process_id($script, $bin)
	{
		exec("ps -ef | grep '$script'", $output);
		
		$procIds = array();
		while (list($opKey, $opItem) = @each($output))
		{
			if (strstr($opItem, "$bin $script"))
			{
				preg_match("/^[^ ]+[ ]+([0-9]+).*$/", $opItem, $pregMatch);
				array_push($procIds, $pregMatch[1]);
			}
		}
		return $procIds;
	}
	
	// UNIX/LINUX 下获取正在运行的进程数量
	public static function unix_count_process($script, $bin)
	{
		exec("ps -ef | grep '$script'", $output);
		
		$countProc = 0;
		while (list($opKey, $opItem) = @each($output))
		{
			if (strstr($opItem, "$bin $script"))
				$countProc++;
		}
		return $countProc;
	}
}
?>