<?php
class TemplateError
{

	var $conf = NULL;
	var $lang = NULL;

	function TemplateError( $conf = 1, $lang = 1 )
	{
		$this->conf = $conf;
		$this->lang = $lang;
	}

	function setErrorHander( )
	{
		set_error_handler( array(
			$this,
			"handler"
		) );
	}

	function handler( $no, $str, $file, $line, $ctx )
	{
		if ( $no == E_NOTICE || $no == 2048 )
		{
			return FALSE;
		}
		$this->logdata = "Exception message: ".$str."\nError code: ".$no."\n";
		$this->_printStackTrace( );
	}

	function printStackTrace( )
	{
		if ( function_exists( "debug_backtrace" ) )
		{
			$info = debug_backtrace( );
			$this->logdata .= "-- Backtrace --\n";
			foreach ( $info as $trace )
			{
				if ( $trace['function'] != "_internalerrorhandler" && $trace['file'] != __FILE__ )
				{
					$this->logdata .= addslashes( $trace['file'] );
					$this->logdata .= "(".$trace['line']."): ";
					if ( $trace['class'] != "" )
					{
						$this->logdata .= $trace['class'].".";
					}
					$this->logdata .= $trace['function']."\n";
				}
			}
		}
		else
		{
			$this->logdata .= "Stack trace is not available";
		}
		if ( TPL_Error_Display || !defined( "TPL_Error_Display" ) )
		{
			$error_code = time( ).rand( );
			$output .= "<div onclick=\"if(tplerror{$error_code}.style.display=='') {tplerror{$error_code}.style.display='none';} else{tplerror{$error_code}.style.display='';}\" style='cursor: hand;font-family: Arial; font-size: 12px;background-color: #FFFFE1;border: 1px dashed #CFCFCF;padding:5px;font-weight: bold;color:red;'>[CMS] Template Running Error, click here to learn more.</div>";
			$output .= "<div id='tplerror{$error_code}' style=\"display:none;background-color: #FFFFE1;border: 1px dashed #CFCFCF;font-family: Courier New; font-size: 12px;padding-left:10px;padding-top:10px;\">";
			$output .= nl2br( $this->logdata );
			$output .= "</div>";
			echo $output;
		}
		$this->logFile( $this->logdata );
	}

	function raiseError( $msg, $no = E_USER_WARNING, $type = "label" )
	{
		global $_LANG_SYS;
		global $_Error_vars;
		if ( $type == "str" )
		{
			trigger_error( $msg, E_USER_WARNING );
		}
		else if ( $type == "label" )
		{
			if ( !empty( $_Error_vars ) )
			{
				extract( $_Error_vars, EXTR_PREFIX_SAME, "cms_" );
			}
			$msg = $_LANG_SYS[$msg];
			eval( "\$msg = \"{$msg}\";" );
			trigger_error( $msg, E_USER_WARNING );
		}
	}

	function logFile( $data )
	{
		global $SYS_CONFIG;
		if ( $SYS_CONFIG['enable_error_log'] == FALSE )
		{
			return TRUE;
		}
		$filename = "tplerror.".date( "Ymd" ).".log.php";
		$data = "- - - - - - - - - - - - - - - - ".date( "Y-m-d H:i:s" )."- - - - - - - - - - - - - - - - \n".$data."\n";
		if ( is_writable( SYS_PATH."sysdata/logs" ) )
		{
			$filename = SYS_PATH."sysdata/logs/".$filename;
		}
		else
		{
			$filename = SYS_PATH."sysdata/".$filename;
		}
		if ( !file_exists( $filename ) && ( $handle = fopen( $filename, "a" ) ) )
		{
			fwrite( $handle, "<?php exit('Access Denied!'); ?>\n" );
			fclose( $handle );
		}
		if ( $handle = fopen( $filename, "a" ) )
		{
			fwrite( $handle, $data."\n" );
			fclose( $handle );
		}
	}

	function addVar( $key, $var )
	{
		global $_Error_vars;
		$_Error_vars[$key] = $var;
	}

	function flushVar( )
	{
		$_Error_vars = "";
	}
}
?>
