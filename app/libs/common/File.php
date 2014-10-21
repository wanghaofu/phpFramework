<?php

/******************************************************************

Name: 文件处理函数库
Author: 王涛 ( tony)
Email: wanghaofu@163.com
QQ: 595900598

 ******************************************************************/
class File
{
	// 读取文件
	public static function read_file($file)
	{
		if (substr($file, 0, 1) == '/')
		{
			$file = '.' . $file;
		}
		$data = @file_get_contents($file);
		return $data;
	}
	
	// 写入文件
	public static function write_file($fileName, $data, $method = "w")
	{
		$fp = @fopen($fileName, $method);
		if (!$fp)
		{
			return false;
		}
		// flock ( $fp, LOCK_EX );
		$op = @fwrite($fp, $data);
		fclose($fp);
		return $op;
	}
	
	// 格式化文件大小
	public static function format_size($sizeInput)
	{
		$sizeInput = doubleval($sizeInput);
		if ($sizeInput >= 1024 * 1024 * 1024)
		{
			$sizeOutput = sprintf("%01.2f", $sizeInput / (1024 * 1024 * 1024)) . " GB";
		}
		elseif ($sizeInput >= 1024 * 1024)
		{
			$sizeOutput = sprintf("%01.2f", $sizeInput / (1024 * 1024)) . " MB";
		}
		elseif ($sizeInput >= 1024)
		{
			$sizeOutput = sprintf("%01.2f", $sizeInput / 1024) . " KB";
		}
		else
		{
			$sizeOutput = $sizeInput . " Bytes";
		}
		return ($sizeOutput);
	}
	
	// 获取文件路径
	public static function get_file_path($file)
	{
		$parts = explode('/', str_replace("\\", "/", $file));
		array_pop($parts);
		$path = join('/', $parts);
		return ($path);
	}
	
	// 获取文件名
	public static function get_file_name($file)
	{
		$parts = explode('.', basename($file));
		array_pop($parts);
		$fileName = join('.', $parts);
		return ($fileName);
	}
	
	// 获取扩展名
	public static function get_file_ext($file)
	{
		$parts = explode('.', $file);
		$ext = array_pop($parts);
		return ($ext);
	}
	
	// 遍历操作目录
	// $op = LIST_DIR / LIST_FILE / COUNT_FILE / GET_SIZE / DEL
	public static function deal_dir($root = './', $path = '', $op = '', $level = 0, $curLevel = 0, $keyword = '')
	{
		if ($level > 0 && $curLevel >= $level)
		{
			return FALSE;
		}
		
		if ($op == '')
		{
			$op = "LIST_DIR,LIST_FILE,COUNT_FILE,COUNT_DIR,GET_SIZE";
		}
		
		if (!is_array($op))
		{
			$op = @explode(',', str_replace(' ', '', $op));
		}
		
		if (!$dir = @dir($root . $path))
		{
			return FALSE;
		}
		
		$dirSerial = 0;
		$arrayReturn = array();
		while (($subItem = $dir->read()) !== false)
		{
			if ($keyword != '' && !strstr(strtolower($subItem), strtolower($keyword)))
			{
				continue;
			}
			
			if (is_file("$root$path/$subItem"))
			{
				if (in_array('COUNT_FILE', $op))
				{
					$arrayReturn['COUNT_FILE']++;
				}
				if (in_array('GET_SIZE', $op))
				{
					$arrayReturn['GET_SIZE'] += filesize("$root$path/$subItem");
				}
				if (in_array('DEL', $op))
				{
					@unlink("$root$path/$subItem");
				}
				if (in_array('LIST_FILE', $op))
				{
					$arrayReturn['LIST_FILE'] .= "\n$path/$subItem";
				}
				if (in_array('FILE', $op))
				{
					$arrayReturn['FILE'][$subItem]['FILESIZE'] = filesize("$root$path/$subItem");
					$arrayReturn['FILE'][$subItem]['FILETIME'] = filemtime("$root$path/$subItem");
					$arrayReturn['FILE'][$subItem]['PATH'] = "$path/$subItem";
				}
			}
			elseif ($subItem != '.' && $subItem != '..' && is_dir("$root$path/$subItem"))
			{
				$subRet = DoDir($root, "$path/$subItem", $opt, $Level, $curLevel + 1);
				
				if (in_array('COUNT_FILE', $op))
				{
					$arrayReturn['COUNT_FILE'] += $subRet['COUNT_FILE'];
				}
				if (in_array('COUNT_DIR', $op))
				{
					$arrayReturn['COUNT_DIR']++;
				}
				if (in_array('GET_SIZE', $op))
				{
					$arrayReturn['GET_SIZE'] += $subRet['GET_SIZE'];
				}
				if (in_array('DEL', $op))
				{
					@rmdir("$root$path/$subItem");
				}
				if (in_array('LIST_DIR', $op))
				{
					$arrayReturn['LIST_DIR"] .= "\n$path/$subItem" . $subRet["LIST_DIR'];
				}
				if (in_array('LIST_FILE', $op))
				{
					$arrayReturn['LIST_FILE'] .= $subRet['LIST_FILE'];
				}
				if (in_array('DIR', $op))
				{
					$arrayReturn['DIR'][$subItem]['DIR'] = $subRet['DIR'];
					$arrayReturn['DIR'][$subItem]['DIRTIME'] = filemtime("$root$path/$subItem");
					$arrayReturn['DIR'][$subItem]['PATH'] = "$path/$subItem";
					$arrayReturn['DIR'][$subItem]['SERIAL'] = $dirSerial++;
				}
				if (in_array('FILE', $op))
				{
					$arrayReturn['DIR'][$subItem]['FILE'] = $subRet['FILE'];
				}
			}
		}
		
		$dir->close();
		
		if (in_array('DEL', $op) && is_dir($root . $path))
		{
			@rmdir($root . $path);
		}
		
		$arrayReturn['COUNT_FILE'] = intval($arrayReturn['COUNT_FILE']);
		$arrayReturn['COUNT_DIR'] = intval($arrayReturn['COUNT_DIR']);
		
		return $arrayReturn;
	}
	
	// 批量删除
	public static function batch_delete($file, $path = '.')
	{
		$regfile = String::to_reg_pattern($file);
		$regfile = "/$regfile/";
		
		if (!is_dir($path))
			return false;
		$hDir = @dir($path);
		if ($hDir)
		{
			while (($hFile = $hDir->read()) !== false)
			{
				if (preg_match($regfile, $hFile))
				{
					$filePath = $path . '/' . $hFile;
					@unlink($filePath);
				}
			}
			$hDir->close();
		}
	}
	
	// 批量建立目录
	public static function batch_mkdir($root, $path, $mod = 0777)
	{
		$arrayPath = explode('/', str_replace("\\", "/", $path));
		$curPath = $root;
		while (list($key, $item) = @each($arrayPath))
		{
			$curPath .= ($root != '' || $key > 0) ? "/$item" : $item;
			if (!is_dir($curPath))
			{
				@mkdir($curPath, $mod);
				@chmod($curPath, $mod);
			}
		}
	}
	
	// 批量建立 FTP 目录
	public static function batch_ftp_mkdir($connFtp, $root, $path)
	{
		$arrayPath = explode('/', str_replace("\\", "/", $path));
		$curPath = $root;
		while (list($key, $item) = @each($arrayPath))
		{
			$curPath .= $curPath ? "/$item" : $item;
			@ftp_mkdir($connFtp, $curPath);
		}
	}
	
	// 输出或保存日志信息
	public static function log($log, $fileName = null)
	{
		$date = date("Y/m/d H:i:s");
		$log = $date . "\t" . $log . "\n";
		
		if (is_null($fileName) || !trim($fileName))
		{
			echo $log;
		}
		else
		{
			self::write_file($fileName, $log, 'a');
		}
	}
	
	public static function debug_log($e)
	{
		//		$traceArr = debug_backtrace(); //程序异常
		//		$message = print_r($message, true);
		//		$log .= "errorTraceFile :\n\t";
		//		array_shift($traceArr); //去掉追踪本身错误
		

		//		for($i = 1; $i <= 6; $i ++) //弹出出事调用前5个 amf系统错误
		//		{
		//			array_pop ( $traceArr );
		//	
		//		de($e);
		$log = '';
		$newline = "";
		if ($e instanceof Execption)
		{
			$message = $e->getMessage();
			$file = $e->getFile();
			$line = $e->getLine();
			$traceInfo = $e->getTrace();
			
			$traceInfoStr = $e->getTraceAsString();
			//			$log .= $traceInfoStr;
			$log .= " MESSAGE: $message 
	FILE :$file $newline  LINE : $line {$newline}";
			$log .= "\n\t##TRACE INFO START..... ";
						$log .= $traceInfoStr;
			foreach ($traceInfo as $key => $value)
			{
				if (!empty($value['args']) && is_array( $value['args']))
				{
					$args = implode(', ', $value['args']);
				}
				$log .= "\n\t#{$key} FILE : {$value['file']} LINE : {$value['line']}  {$value['class']} :: {$value['function']} (  $args )";
			}
			if (DEBUG_MODE == true)
			{
				echo '<pre>';
				echo $log;
				echo '</pre>';
			}
		}
		else
		{
			$log .= $e;
		}
		self::log($log, LOG_PATH);
	
	}
}

?>