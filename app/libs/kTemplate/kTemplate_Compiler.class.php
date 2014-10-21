<?php
class kTemplate_Compiler extends kTemplate
{
	var $_parseFunArray = array
	(
		0 => "parse_meta",
		1 => "parse_literal_encode",
		2 => "parse_html_tag_input_radio",
		3 => "parse_html_tag_select",
		4 => "parse_xml",
		5 => "parse_if_cmsware3",
		6 => "parse_if",
		7 => "parse_loop",
		8 => "parse_loop_cmsware3",
		9 => "parse_tag",
		10 => "parse_tag_func",
		11 => "parse_include_subtpl",
		12 => "parse_include",
		13 => "parse_get",
		14 => "parse_var",
		15 => "parse_op",
		16 => "parse_debug",
		17 => "parse_header",
		18 => "parse_literal_decode",
		19 => "clear_comment",
		20 => "parse_php"
	);

	function kTemplate_Compiler( $params = NULL )
	{
		if ( isset( $params['template_dir'] ) )
		{
			$this->template_dir = $params['template_dir'];
		}
		if ( isset( $params['compile_dir'] ) )
		{
			$this->compile_dir = $params['compile_dir'];
		}
		if ( isset( $params['cache_dir'] ) )
		{
			$this->cache_dir = $params['cache_dir'];
		}
		if ( isset( $params['lang_dir'] ) )
		{
			$this->lang_dir = $params['lang_dir'];
		}
	}

	function format( $file_name )
	{
		$file_name = str_replace( ":", "_", $file_name );
		$file_name = str_replace( "/", "@", $file_name );
		$file_name = str_replace( "\\", "@", $file_name );
		$file_name = str_replace( "..", "^", $file_name );
		return $file_name;
	}

	function readTemplate( $file_name )
	{
		if ( empty( $this->source ) )
		{
			if ( $this->templateExists( $file_name ) )
			{
				$fp = fopen( $file_name, "r" );
				$contents = fread( $fp, filesize( $file_name ) );
				fclose( $fp );
				return $contents;
			}
			else
			{
				exit( "<b>kTemplate error:</b> Unable to read template file: <b>".$file_name."</b>" );
			}
		}
		else
		{
			$source =& $this->source;
			return $this->$source( $file_name );
		}
	}

	function db( &$file_name )
	{
		list( $tId, $tName ) = explode( "@", $file_name );
		return tplAdmin::gettplcontent( $tId );
	}

	function makeDir( $directory, $mode = 511 )
	{
		if ( @opendir( $directory ) )
		{
			return true;
		}
		if ( @mkdir( $directory, $mode ) )
		{
			return true;
		}
		else
		{
			$pathInfo = explode( "/", $directory );
			$basedir = "";
			foreach ( $pathInfo as $var )
			{
				if ( $var == "." )
				{
					$basedir .= "./";
					$begin = false;
				}
				else
				{
					if ( $var == ".." )
					{
						$basedir .= "../";
						$begin = false;
					}
					else
					{
						if ( !$begin )
						{
							$var = $var;
							$begin = true;
						}
						else
						{
							$var = "/".$var;
						}
						if ( $this->makeDir( $basedir.$var, $mode ) )
						{
							echo "Repair {$basedir}{$var} <br>";
							$repair = true;
							$basedir .= $var;
						}
						else
						{
							$repair = false;
						}
					}
				}
			}
			return $repair;
		}
	}

	function templateExists( $file_name )
	{
		if ( file_exists( $file_name ) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function compile( $file_name, $compile_name = NULL )
	{
		$data = pathinfo( $file_name );
		if ( !$this->templateExists( $file_name ) )
		{
			echo "<b>kTemplate error:</b> Template file dose not exists: <b> ".$file_name."</b>";
			return false;
		}
		else if ( !empty( $compile_name ) )
		{
			if ( file_exists( $this->compile_dir.$compile_name ) )
			{
				$expire = filemtime( $file_name ) == filemtime( $this->compile_dir.$compile_name ) ? true : false;
				if ( $expire )
				{
					return true;
				}
			}
		}
		else if ( file_exists( $this->compile_dir.$this->compilefile_prefix.$data['basename'] ) )
		{
			$expire = filemtime( $file_name ) == filemtime( $this->compile_dir.$this->compilefile_prefix.$data['basename'] ) ? true : false;
			if ( $expire )
			{
				return true;
			}
		}
		$content = $this->readTemplate( $file_name );
		$content = $this->_compile_file( $content );
		if ( !empty( $compile_name ) )
		{
			if ( $fp = fopen( $this->compile_dir.$compile_name, "w" ) )
			{
				fwrite( $fp, $content );
				fclose( $fp );
				touch( $this->compile_dir.$compile_name, filemtime( $file_name ) );
				return true;
			}
			else
			{
				exit( "<b>kTemplate error:</b> Unable to write compiled file : <b>".$this->compile_dir.$this->format( $compile_name )."</b>" );
			}
		}
		else if ( $fp = fopen( $this->compile_dir.$this->compilefile_prefix.$data['basename'], "w" ) )
		{
			fwrite( $fp, $content );
			fclose( $fp );
			touch( $this->compile_dir.$this->compilefile_prefix.$data['basename'], filemtime( $file_name ) );
			return true;
		}
		else
		{
			exit( "<b>kTemplate error:</b> Unable to write compiled file : <b>".$this->compile_dir.$this->compilefile_prefix.$this->format( $file_name )."</b>" );
		}
	}

	function _compile_file( $contents )
	{
		if ( !empty( $this->regPreFilterArray ) )
		{
			foreach ( $this->regPreFilterArray as $var )
			{
				if ( function_exists( $var ) )
				{
					$contents = $var( $contents );
				}
			}
		}
		foreach ( $this->_parseFunArray as $var )
		{
			$contents = $this->$var( $contents );
		}
		if ( !empty( $this->registerParseFunArray ) )
		{
			foreach ( $this->registerParseFunArray as $var )
			{
				if ( function_exists( $var ) )
				{
					$contents = $var( $contents );
				}
			}
		}
		if ( $this->compile_lang )
		{
			$this->parse_lang( $contents );
			$this->parse_lang_php( $contents );
		}
		return $contents;
	}

	function _compile_php( &$contents )
	{
		$patt = "'<php>(.*)</php>'siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$contents = str_replace( $var, base64_decode( $matches[1][$key] ), $contents );
			}
		}
		return $contents;
	}

	function parse_lang( &$contents )
	{
		$patt = "/\\{lang:([a-zA-Z0-9_\\-]+)\\}/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], $this->lang_tag_format( $var ), $contents );
			}
		}
		$patt = "/\\{lang::global:([a-zA-Z0-9_\\-\\s]+)\\}/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], $this->lang_global_tag_format( $var ), $contents );
			}
		}
	}

	function parse_lang_php( &$contents )
	{
		$patt = "/#lang:([a-zA-Z0-9_\\-]+)#/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], $this->lang_php_tag_format( $var ), $contents );
			}
		}
		$patt = "/#lang::global:([a-zA-Z0-9_\\-\\s]+)#/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], $this->lang_php_global_tag_format( $var ), $contents );
			}
		}
	}

	function lang_php_tag_format( $var )
	{
		$var = "\$_LANG_SKIN['{$var}']";
		return $var;
	}

	function lang_php_global_tag_format( $var )
	{
		if ( strpos( $var, " " ) )
		{
			$vars = explode( " ", $var );
			foreach ( $vars as $key => $var1 )
			{
				$return .= "\$_LANG_SKIN_GLOBAL['{$var1}']";
			}
		}
		else
		{
			$return = "\$_LANG_SKIN_GLOBAL['{$var}']";
		}
		return $return;
	}

	function lang_tag_format( $var )
	{
		$var = "<?php echo \$_LANG_SKIN['{$var}'];?>";
		return $var;
	}

	function lang_global_tag_format( $var )
	{
		if ( strpos( $var, " " ) )
		{
			$vars = explode( " ", $var );
			foreach ( $vars as $key => $var1 )
			{
				$return .= "<?php echo \$_LANG_SKIN_GLOBAL['{$var1}'];?>";
			}
		}
		else
		{
			$return = "<?php echo \$_LANG_SKIN_GLOBAL['{$var}'];?>";
		}
		return $return;
	}

	function parse_tag( $contents )
	{
		$patt = "/".preg_quote( $this->tag_left_delim )."\\\$([\\S]+)".preg_quote( $this->tag_right_delim )."/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], $this->parse_tag_format_display( $var ), $contents );
			}
		}
		$patt = "/".preg_quote( "{" )."\\\$(.*)".preg_quote( "}" )."/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				if ( strpos( $matches[0][$key], "this->_tpl_vars" ) )
				{
					continue;
				}
				$contents = str_replace( $matches[0][$key], $this->parse_tag_format_var( $var ), $contents );
			}
		}
		$patt = "/".preg_quote( $this->tag_left_delim )."\\*(.*)".preg_quote( $this->tag_right_delim )."/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], $this->parse_tag_format_global_display( $var ), $contents );
			}
		}
		$patt = "/".preg_quote( "{" )."\\*(.*)".preg_quote( "}" )."/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				if ( strpos( $matches[0][$key], "this->_tpl_vars" ) )
				{
					continue;
				}
				$contents = str_replace( $matches[0][$key], $this->parse_tag_format_global_var( $var ), $contents );
			}
		}
		return $contents;
	}

	function parse_tag_format_var( $string )
	{
		$header = "{\$this->_tpl_vars";
		if ( strpos( $string, "." ) )
		{
			$data = explode( ".", $string );
			$string = "";
			foreach ( $data as $key => $var )
			{
				$var = $this->parse_tag_format_varIN( $var );
				$string .= "[\"".$var."\"]";
			}
			$string = $header.$string;
		}
		else
		{
			$string = $header."['".$string."']";
		}
		return $string."}";
	}

	function parse_tag_format_var2( $string )
	{
		$header = "\$this->_tpl_vars";
		if ( strpos( $string, "." ) )
		{
			$data = explode( ".", $string );
			$string = "";
			foreach ( $data as $key => $var )
			{
				$var = $this->parse_tag_format_varIN( $var );
				$string .= "[\"".$var."\"]";
			}
			$string = $header.$string;
		}
		else
		{
			$string = $header."['".$string."']";
		}
		return $string;
	}

	function parse_tag_format_varIN( $string )
	{
		$header = "{\$this->_tpl_vars";
		$substr = substr( $string, 0, 1 );
		if ( strpos( $string, ":" ) && $substr == "\$" )
		{
			$string = substr( $string, 1 );
			$data = explode( ":", $string );
			$string = "";
			foreach ( $data as $key => $var )
			{
				$string .= "['".$var."']";
			}
			$string = $header.$string."}";
		}
		return $string;
	}

	function parse_tag_format_display( $string )
	{
		$header = "<?php echo \$this->_tpl_vars";
		if ( strpos( $string, "." ) )
		{
			$data = explode( ".", $string );
			$string = "";
			foreach ( $data as $key => $var )
			{
				$var = $this->parse_tag_format_varIN( $var );
				$string .= "[\"".$var."\"]";
			}
			$string = $header.$string.";?>";
		}
		else
		{
			$string = $header."[\"".$string."\"];?>";
		}
		return $string;
	}

	function parse_tag_format_global_var( $string )
	{
		$header = "{\$GLOBALS";
		if ( strpos( $string, "." ) )
		{
			$data = explode( ".", $string );
			$string = "";
			foreach ( $data as $key => $var )
			{
				$string .= "[\"".$var."\"]";
			}
			$string = $header.$string.";?>";
		}
		else
		{
			$string = $header."[\"".$string."\"];?>";
		}
		return $string."}";
	}

	function parse_tag_format_global_display( $string )
	{
		$header = "<?php echo \$GLOBALS";
		if ( strpos( $string, "." ) )
		{
			$data = explode( ".", $string );
			$string = "";
			foreach ( $data as $key => $var )
			{
				$string .= "['".$var."']";
			}
			$string = $header.$string.";?>";
		}
		else
		{
			$string = $header."['".$string."'];?>";
		}
		return $string;
	}

	function _parseParameterPlus( $Parameter )
	{
		$Parameter = trim( $Parameter );
		if ( preg_match_all( "/([A-Za-z0-9_\\-]+)=[\"]([^\"\\']+)[\"]/siU", $Parameter, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$returnKey = strtolower( $matches[1][$key] );
				$return[$returnKey] = $matches[2][$key];
			}
		}
		if ( preg_match_all( "/([A-Za-z0-9_\\-]+)=[\\']([^\"\\']+)[\\']/siU", $Parameter, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$returnKey = strtolower( $matches[1][$key] );
				$return[$returnKey] = $matches[2][$key];
			}
		}
		if ( preg_match_all( "/([A-Za-z0-9_\\-]+)=([^\"\\']+)[\\s]+/siU", $Parameter, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$returnKey = strtolower( $matches[1][$key] );
				$return[$returnKey] = trim( $matches[2][$key] );
			}
		}
		return $return;
	}

	function parse_meta( &$contents )
	{
		if ( defined( "CMSWARE_VERSION" ) && defined( "ADD_META_MARK" ) )
		{
			$patt = "/<meta[\\s]+(.*)[\\s]*[\\/]?>/siU";
			$add_meta_position = "";
			if ( preg_match_all( $patt, $contents, $matches ) )
			{
				$do_keywords_mark = false;
				$do_description_mark = false;
				foreach ( $matches[1] as $key => $var )
				{
					$attributes = $this->_parseParameterPlus( $matches[1][$key] );
					$replace = "";
					$do_mark = false;
					foreach ( $attributes as $keyIn => $varIn )
					{
						if ( $keyIn == "name" )
						{
							$varIn = strtolower( trim( $varIn ) );
							if ( $varIn == "generator" )
							{
								$do_keywords_mark = true;
								$do_mark = true;
							}
						}
						else if ( $keyIn == "http-equiv" && strtolower( trim( $varIn ) ) == "content-type" )
						{
							$add_meta_position = $matches[0][$key];
						}
					}
					if ( $do_mark )
					{
						if ( strtolower( $attributes['name'] ) == "generator" )
						{
//							$replace = "<meta name=\"generator\" content=\"".$attributes['content']."\" />";
						}
						$contents = str_replace( $matches[0][$key], $replace, $contents );
					}
				}
				if ( empty( $add_meta_position ) )
				{
					if ( !$do_keywords_mark )
					{
//						$contents = preg_replace( "'(</title>)'isU", "\\1\n<meta name=\"generator\" content=\"".CMSWARE_VERSION."\" /> ", $contents );
					}
				}
				else if ( !$do_keywords_mark )
				{
//					$contents = str_replace( $add_meta_position, $add_meta_position."\n<meta name=\"generator\" content=\"".CMSWARE_VERSION."\" /> ", $contents );
				}
			}
		}
		return $contents;
	}

	function parse_php( &$contents )
	{
		$patt = "'<php>(.*)</php>'siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				if ( $this->is_compile_php )
				{
					$contents = str_replace( $matches[1][$key], base64_encode( $matches[1][$key] ), $contents );
				}
				else
				{
					$contents = str_replace( $matches[0][$key], $matches[1][$key], $contents );
				}
			}
		}
		return $contents;
	}

	function parse_if( &$contents )
	{
		$patt = "'<if[\\s]+([^\n]+)>'si";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$str = "<?php if(".$matches[1][$key]."): ?>";
				$data = $this->format_var( $str );
				$contents = str_replace( $var, $data, $contents );
			}
		}
		$patt = "'<elseif[\\s]+([^\n]+)>'si";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$str = "<?php elseif(".$matches[1][$key]."): ?>";
				$data = $this->format_var( $str );
				$contents = str_replace( $var, $data, $contents );
			}
		}
		$patt = "'<else>'siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$data = "<?php else: ?>";
				$contents = str_replace( $var, $data, $contents );
			}
		}
		$patt = "'</if>'siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$data = "<?php endif;?>";
				$contents = str_replace( $var, $data, $contents );
			}
		}
		return $contents;
	}

	function parse_if_cmsware3( &$contents )
	{
		$patt = "'<if[\\s]+test=\"([^\"]+)\"[\\s]*>'si";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$str = "<?php if(".$matches[1][$key]."): ?>";
				$data = $this->format_var( $str );
				$contents = str_replace( $var, $data, $contents );
			}
		}
		$patt = "'<elseif[\\s]+test=\"([^\"]+)\"[\\s]*>'si";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$str = "<?php elseif(".$matches[1][$key]."): ?>";
				$data = $this->format_var( $str );
				$contents = str_replace( $var, $data, $contents );
			}
		}
		$patt = "'<else>'siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$data = "<?php else: ?>";
				$contents = str_replace( $var, $data, $contents );
			}
		}
		$patt = "'</if>'siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$data = "<?php endif;?>";
				$contents = str_replace( $var, $data, $contents );
			}
		}
		return $contents;
	}

	function format_var( $data )
	{
		$patt = "/([^[]?)\\\$([a-zA-Z0-9_\\-\\.\$:]+)/";
		if ( preg_match_all( $patt, $data, $matches ) )
		{
			$matches[2] = array_unique( $matches[2] );
			arsort( $matches[2] );
			foreach ( $matches[2] as $key => $var )
			{
				if ( $var == "this-" )
				{
					continue;
				}
				if ( $var == "GLOBALS" )
				{
					continue;
				}
				$data = preg_replace( "/\\\$".preg_quote( $matches[2][$key] )."([^a-zA-Z0-9_\\-\\.])?/", $this->format_control_local( $var )."\\1", $data );
			}
		}
		$patt = "/([^[]?)\\*([a-zA-Z0-9_\\-\\.]+)/";
		if ( preg_match_all( $patt, $data, $matches ) )
		{
			foreach ( $matches[2] as $key => $var )
			{
				$data = preg_replace( "/\\\$".preg_quote( $matches[2][$key] )."([^a-zA-Z0-9_\\-\\.])/", $this->format_control_local( $var )."\\1", $data );
				$data = str_replace( $matches[0][$key]." ", $matches[1][$key].$this->format_control_global( $var )." ", $data );
			}
		}
		return $data;
	}

	function format_control_local( $string )
	{
		$header = "\$this->_tpl_vars";
		if ( strpos( $string, "." ) )
		{
			$data = explode( ".", $string );
			$string = "";
			foreach ( $data as $key => $var )
			{
				$var = $this->parse_tag_format_varIN( $var );
				$string .= "[\"".$var."\"]";
			}
			$string = $header.$string;
		}
		else
		{
			$string = $header."['".$string."']";
		}
		return $string;
	}

	function format_control_global( $string )
	{
		$header = "\$GLOBALS";
		if ( strpos( $string, "." ) )
		{
			$data = explode( ".", $string );
			$string = "";
			foreach ( $data as $key => $var )
			{
				$string .= "['".$var."']";
			}
			$string = $header.$string;
		}
		else
		{
			$string = $header."['".$string."']";
		}
		return $string;
	}

	function parse_loop( &$contents )
	{
		$patt = "'<loop[\\s]+([\\S]+)[\\s]+var=([a-zA-Z0-9_\\-]+)[\\s]*>'siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$str = "<?php if(!empty(".$matches[1][$key]." )): \n foreach (".$matches[1][$key]." as  \$this->_tpl_vars['".$matches[2][$key]."']): ?>";
				$data = $this->format_var( $str );
				$contents = str_replace( $var, $data, $contents );
			}
		}
		$patt = "'<loop[\\s]+([\\S]+)[\\s]+key=([a-zA-Z0-9_\\-]+)[\\s]+var=([a-zA-Z0-9_\\-]+)[\\s]*>'siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$str = "<?php if(!empty(".$matches[1][$key]." )): \n foreach (".$matches[1][$key]." as  \$this->_tpl_vars['".$matches[2][$key]."']=>\$this->_tpl_vars['".$matches[3][$key]."']): ?>";
				$data = $this->format_var( $str );
				$contents = str_replace( $var, $data, $contents );
			}
		}
		$patt = "'<loop[\\s]+([\\S]+)[\\s]+var=([a-zA-Z0-9_\\-]+)[\\s]+key=([a-zA-Z0-9_\\-]+)[\\s]*>'siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$str = "<?php if(!empty(".$matches[1][$key]." )): \n foreach (".$matches[1][$key]." as  \$this->_tpl_vars['".$matches[3][$key]."']=>\$this->_tpl_vars['".$matches[2][$key]."']): ?>";
				$data = $this->format_var( $str );
				$contents = str_replace( $var, $data, $contents );
			}
		}
		$patt = "'</loop>'siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$data = "<?php endforeach; endif;?>";
				$contents = str_replace( $var, $data, $contents );
			}
		}
		return $contents;
	}

	function parse_loop_cmsware3( &$contents )
	{
		$patt = "'<loop[\\s]+(.*)>'siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$params = $this->_parseAttribute( $matches[1][$key] );
				if ( isset( $params['start'] ) )
				{
					$str = "<?php if(!empty(\${$params['name']} )): \n\n \$kTemplate_i = {$params['start']};\n \n\n \$kTemplate_data = array();\n foreach(\${$params['name']} as \$kTemplate_key=>\$kTemplate_var) {\n\n \$kTemplate_data[\"{\$kTemplate_i}\"] = \$kTemplate_var; \n\n \$kTemplate_i++; \n\n}\n\n \${$params['name']}=\$kTemplate_data;\n foreach (\${$params['name']} as  \$this->_tpl_vars['{$params['key']}']=>\$this->_tpl_vars['{$params['var']}']): ?>";
				}
				else
				{
					$str = "<?php if(!empty(\${$params['name']} )): \n   foreach (\${$params['name']} as  \$this->_tpl_vars['{$params['key']}']=>\$this->_tpl_vars['{$params['var']}']): ?>";
				}
				$data = $this->format_var( $str );
				$contents = str_replace( $var, $data, $contents );
			}
		}
		$patt = "'</loop>'siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$data = "<?php endforeach; endif;?>";
				$contents = str_replace( $var, $data, $contents );
			}
		}
		return $contents;
	}

	function _parseAttribute( $Parameter )
	{
		$pattern = "/([a-zA-Z0-9_]+)=[\"]([^\"]+)[\"]/isU";
		if ( preg_match_all( $pattern, $Parameter, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$output[strtolower( $matches[1][$key] )] = $matches[2][$key];
			}
		}
		return $output;
	}

	function clear_comment( $contents )
	{
		return preg_replace( "/<!--#CW#(.*)#CW#-->/isU", "", $contents );
	}

	function parse_include_subtpl( $contents )
	{
		$patt = "/<include[\\s]+(.*)[\\s]*[\\/]?>/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$attributes = $this->_parseRawParameter( $matches[1][$key] );
				$replace = "";
				foreach ( $attributes as $keyIn => $varIn )
				{
					if ( $keyIn == "file" )
					{
						$file_replace = $this->parse_include_format( $varIn );
					}
					else if ( $varIn[0] == "\$" )
					{
						$varIn = substr( $varIn, 1 );
						$replace .= "\n<?php \$this->_tpl_vars['{$keyIn}'] = ".$this->parse_tag_format_var2( $varIn )." ;?>\n";
					}
					else
					{
						$replace .= "\n<?php \$this->_tpl_vars['{$keyIn}'] = \"{$varIn}\";?>\n";
					}
				}
				$contents = str_replace( $matches[0][$key], $replace.$file_replace, $contents );
			}
		}
		return $contents;
	}

	function parse_include( $contents )
	{
		$patt = "/".$this->left_delimiter."include[:]?[\\s]+file=(.*)[\\s]*[\\/]?".$this->right_delimiter."/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], $this->parse_include_format( $var ), $contents );
			}
		}
		return $contents;
	}

	function path2name( $string )
	{
		$string = str_replace( "./", "", $string );
		$string = str_replace( "\"", "", $string );
		$string = str_replace( "/", "_", $string );
		$string = str_replace( "\\", "_", $string );
		$string = str_replace( "..", "--", $string );
		$string = str_replace( ".", "-", $string );
		$string = str_replace( ":", "%", $string );
		return $string;
	}

	function parse_include_format( $string )
	{
		$header = "<?php include(\$this->compile_dir.\"";
		$string = str_replace( "\"", "", $string );
		$string = str_replace( "'", "", $string );
		$string = str_replace( " ", "", $string );
		if ( strpos( "hawking".$string, "file:" ) )
		{
			$string = str_replace( "file:", "", $string );
			$new_name = $this->compilefile_prefix.$this->format( $string );
			if ( $this->compile( $string, $new_name ) )
			{
				$string = "<?php include(\"".$new_name."\");?>";
			}
			else
			{
				$string = "<b>kTemplate error : </b>unable to compile template: <b>{$string}</b>"."<?php include(\"".$new_name."\");?>";
			}
		}
		else if ( strpos( "hawking".$string, "http://" ) )
		{
			$string = "<?php include(\"".$string."\");?>";
		}
		else if ( strpos( "hawking".$string, "{\$" ) )
		{
			eval( "\$string = \"{$string}\";" );
			$new_name = $this->compilefile_prefix.$this->format( $string );
			if ( $this->compile( $string, $new_name ) )
			{
				$string = "<?php include(\"".$new_name."\");?>";
			}
			else
			{
				$string = "<b>kTemplate error : </b>unable to compile template: <b>{$string}</b>"."<?php include(\"".$new_name."\");?>";
			}
		}
		else if ( strpos( "hawking".$string, "../" ) )
		{
			$pathinfo = pathinfo( $this->template_name );
			$pathinfo['dirname'] = empty( $pathinfo['dirname'] ) ? $this->template_dir : $pathinfo['dirname']."/";
			$num = 0;
			$dir = "";
			$data = explode( "/", $string );
			foreach ( $data as $var )
			{
				if ( $var == ".." )
				{
					++$num;
				}
			}
			$string = str_replace( "../", "", $string );
			$string = str_replace( "./", "", $string );
			$data = explode( "/", $pathinfo['dirname'] );
			$num = count( $data ) - $num - 1;
			$i = 0;
			for ( ;	$i < $num;	++$i	)
			{
				$dir .= $data[$i]."/";
			}
			$new_name = $this->compilefile_prefix.$this->format( $pathinfo['dirname'].$string );
			if ( $this->compile( $dir.$string, $new_name ) )
			{
				$string = "<?php include(\"".$this->compile_dir.$new_name."\");?>";
			}
			else
			{
				$string = "<b>kTemplate error : </b>unable to compile template: <b>{$string}</b>"."<?php include(\"".$this->compile_dir.$new_name."\");?>";
			}
		}
		else if ( substr( $string, 0, 1 ) == "/" )
		{
			$new_name = $this->compilefile_prefix.$this->format( $string );
			if ( $this->compile( $this->template_dir.$string, $new_name ) )
			{
				$string = "<?php include(\"".$this->compile_dir.$new_name."\");?>";
			}
			else
			{
				$string = "<b>kTemplate error : </b>unable to compile template: <b>{$string}</b>"."<?php include(\"".$this->compile_dir.$new_name."\");?>";
			}
		}
		else
		{
			$pathinfo = pathinfo( $this->template_name );
			$pathinfo['dirname'] = empty( $pathinfo['dirname'] ) ? $this->template_dir : $pathinfo['dirname']."/";
			$new_name = $this->compilefile_prefix.$this->format( $pathinfo['dirname'].$string );
			if ( $this->compile( $pathinfo['dirname'].$string, $new_name ) )
			{
				$string = "<?php include(\"".$this->compile_dir.$new_name."\");?>";
			}
			else
			{
				$string = "<b>kTemplate error : </b>unable to compile template: <b>{$string}</b>"."<?php include(\"".$this->compile_dir.$new_name."\");?>";
			}
		}
		return $string;
	}

	function parse_get( $contents )
	{
		$patt = "/".$this->left_delimiter."get:[\\s]+file=(.*)[\\s]*".$this->right_delimiter."/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], $this->parse_get_format( $var ), $contents );
			}
		}
		return $contents;
	}

	function parse_get_format( $string )
	{
		$header = "<?php include(\"".$this->template_dir;
		$string = str_replace( "\"", "", $string );
		$string = str_replace( "'", "", $string );
		$string = str_replace( " ", "", $string );
		$num = 0;
		$dir = "";
		if ( strpos( "hawking".$string, "file:" ) )
		{
			$string = str_replace( "file:", "", $string );
			$string = "<?php include(\"".$string."\");?>";
		}
		else if ( strpos( "hawking".$string, "http://" ) )
		{
			$string = str_replace( "http:\\/\\/", "", $string );
			$string = "<?php include(\"".$string."\");?>";
		}
		else if ( strpos( "hawking".$string, "{\$" ) )
		{
			$string = "<?php include(\"".$string."\");?>";
		}
		else if ( strpos( "hawking".$string, "../" ) )
		{
			$data = explode( "/", $string );
			foreach ( $data as $var )
			{
				if ( $var == ".." )
				{
					++$num;
				}
			}
			$string = str_replace( "../", "", $string );
			$string = str_replace( "./", "", $string );
			$data = explode( "/", $this->template_dir );
			$num = count( $data ) - $num - 1;
			$i = 0;
			for ( ;	$i < $num;	++$i	)
			{
				$dir .= $data[$i]."/";
			}
			$string = "<?php include('".$dir.$string."');?>";
		}
		else
		{
			$string = str_replace( "./", "", $string );
			$string = $header.$string."\");?>";
		}
		return $string;
	}

	function parse_var( &$contents )
	{
		$patt = "/<var[\\s]*(.*)[\\s]*[\\/]?>/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$attributes = $this->_parseParameter( $matches[1][$key] );
				if ( $attributes['value'][0] == "\$" )
				{
					$attributes['value'] = substr( $attributes['value'], 1 );
					$replace = "<?php \$this->_tpl_vars['{$attributes['name']}'] = \$this->_tpl_vars['{$attributes['value']}'] ;?>";
				}
				else
				{
					$replace = "<?php \$this->_tpl_vars['{$attributes['name']}'] = \"{$attributes['value']}\";?>";
				}
				$contents = str_replace( $matches[0][$key], $replace, $contents );
			}
		}
		return $contents;
	}

	function parse_op( &$contents )
	{
		$patt = "/<op[\\s]+exp=\"([^\n]*)\"[\\s]*[\\/]?>/si";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			$starter = "<?php ";
			$ender = " ; ?>\n";
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], $starter.$this->format_var( $matches[1][$key] ).$ender, $contents );
			}
		}
		return $contents;
	}

	function parse_debug( &$contents )
	{
		$patt = "/<debug[\\s]+name=\"([^\"\n]*)\"[\\s]*[\\/]?>/si";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			$starter = "<?php ";
			$ender = "  ?>\n";
			foreach ( $matches[1] as $key => $var )
			{
				$command = "\t\$v = htmlspecialchars(stripslashes(var_export(\$this->_tpl_vars[\"{$matches[1][$key]}\"], TRUE)));\r\n\techo \"<TEXTAREA NAME='' ROWS='5' COLS='50'>{\$v}</TEXTAREA>\";\r\n\t\t\t\t";
				$contents = str_replace( $matches[0][$key], $starter.$command.$ender, $contents );
			}
		}
		return $contents;
	}

	function parse_xml( &$contents )
	{
		$patt = "/<\\?xml(.*)\\?>/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$patt = "/".preg_quote( $this->tag_left_delim )."\\\$([\\S]+)".preg_quote( $this->tag_right_delim )."/siU";
				if ( preg_match_all( $patt, $matches[0][$key], $phpmatches ) )
				{
					foreach ( $phpmatches[0] as $keyIn => $varIn )
					{
						if ( $keyIn == 0 )
						{
							$header = strpos( $matches[0][$key], $phpmatches[0][$keyIn] );
							$output = "<?php echo base64_decode(\"".base64_encode( substr( $matches[0][$key], 0, $header ) )."\");?>";
							$output .= $phpmatches[0][$keyIn];
						}
						else
						{
							$header = strpos( $matches[0][$key], $phpmatches[0][$keyIn] );
							$offset = strlen( $phpmatches[0][$keyIn] );
							$output .= "<?php echo base64_decode(\"".base64_encode( substr( $matches[0][$key], $header, $offset ) )."\");?>";
							$output .= $phpmatches[0][$keyIn];
						}
					}
					$output .= "<?php echo base64_decode(\"".base64_encode( substr( $matches[0][$key], $header + strlen( $phpmatches[0][$keyIn] ) ) )."\");?>";
				}
				else
				{
					$output = "<?php echo base64_decode(\"".base64_encode( $matches[0][$key] )."\");?>";
				}
				$contents = str_replace( $matches[0][$key], $output, $contents );
			}
		}
		return $contents;
	}

	function parse_header( &$contents )
	{
		$patt = "/<header[\\s]+name=\"(.*)\"[\\s]+\\/>/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], "<?php header(\"".$matches[1][$key]."\"); ?>", $contents );
			}
		}
		return $contents;
	}

	function _parseParameter( $Parameter )
	{
		$Parameter = trim( $Parameter );
		$patt = "/([A-Za-z0-9_\\-]+)=[\"\\']([^\"\\']*)[\"\\']/siU";
		if ( preg_match_all( $patt, $Parameter, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$returnKey = strtolower( $matches[1][$key] );
				$return[$returnKey] = $matches[2][$key];
			}
		}
		return $return;
	}

	function _parseRawParameter( $Parameter )
	{
		$Parameter = trim( $Parameter );
		$patt = "/([A-Za-z0-9_\\-]+)=[\"\\']([^\"\\']*)[\"\\']/siU";
		if ( preg_match_all( $patt, $Parameter, $matches ) )
		{
			foreach ( $matches[0] as $key => $var )
			{
				$returnKey = $matches[1][$key];
				$return[$returnKey] = $matches[2][$key];
			}
		}
		return $return;
	}

	function parse_tag_func( $contents )
	{
		$patt = "/".preg_quote( "[" )."@([\\S^(]+)\\(([^]]+)\\)".preg_quote( "]" )."/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], $this->parse_tag_func_format( $var, $matches[2][$key] ), $contents );
			}
		}
		$patt = "/".preg_quote( "[" )."@([\\S^(]+)\\(([^\n]+)\\)@".preg_quote( "]" )."/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], $this->parse_tag_func_format( $var, $matches[2][$key] ), $contents );
			}
		}
		return $contents;
	}

	function parse_tag_func_format( $funName, $params )
	{
		$header = "<?php echo ";
		$patt = "/\\\$([a-zA-Z0-9_\\-\\.]+)/si";
		if ( preg_match_all( $patt, $params, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$length[$key] = strlen( $var );
			}
			arsort( $length );
			foreach ( $length as $key => $var )
			{
				$params = preg_replace( "#".preg_quote( $matches[0][$key] )."([,\\s)]*)#isU", $this->parse_tag_format_var2( $matches[1][$key] )."\\1", $params );
			}
		}
		$string = $header.$funName."(".$params.");?>";
		return $string;
	}

	function parse_html_tag_select( $contents )
	{
		$patt = "/<select(.*)>(.*)<\\/select>/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				if ( preg_match( "/value=\"(.*)\"/isU", $matches[1][$key], $valueMatch ) )
				{
					$value = $valueMatch[1];
					$replace = "<?php\r\n\r\n\$select_value = \"{$value}\";\r\n \$select_value = 'hawking'.md5(\$select_value); \r\n\$\$select_value = 'selected';?>";
					$options = $matches[2][$key];
					if ( preg_match_all( "/<option value=\"(.*)\"[^>]*>/isU", $options, $optionMatches ) )
					{
						foreach ( $optionMatches[0] as $keyIn => $varIn )
						{
							$options = str_replace( $optionMatches[0][$keyIn], "<option value=\"{$optionMatches[1][$keyIn]}\" <?php echo \$hawking".md5( $optionMatches[1][$keyIn] )." ;?>>", $options );
						}
					}
					$output = "<select".$matches[1][$key].">".$replace.$options."</select>";
					$contents = str_replace( $matches[0][$key], $output, $contents );
				}
			}
		}
		return $contents;
	}

	function parse_html_tag_input_radio( $contents )
	{
		$patt = "/<input[\\s]+type=\"radio\"(.*)>[\\s\n]+(.*)<\\/input>/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				if ( preg_match_all( "/([a-zA-Z0-9_-]+)=\"(.*)\"/isU", $matches[1][$key], $attributeMatch ) )
				{
					foreach ( $attributeMatch[0] as $keyAttribute => $varAttribute )
					{
						$attributes[strtolower( $attributeMatch[1][$keyAttribute] )] = $attributeMatch[2][$keyAttribute];
					}
				}
				$replace = "<?php\r\n\r\n\$select_value = \"{$attributes['value']}\";\r\n \$select_value = '{$attributes['name']}'.\$select_value; \r\n\$\$select_value = 'checked';?>";
				if ( preg_match_all( "/<option value=\"(.*)\"[^>]*>(.*)<\\/option>/isU", $matches[2][$key], $optionMatches ) )
				{
					foreach ( $optionMatches[0] as $keyIn => $varIn )
					{
						$value = $optionMatches[1][$keyIn];
						$label = $optionMatches[2][$keyIn];
						$replace .= "<input type=\"radio\" name=\"{$attributes['name']}\" value=\"{$value}\" id=\"{$attributes['name']}{$keyIn}\" <?php echo \${$attributes['name']}{$value} ;?>><label for=\"{$attributes['name']}{$keyIn}\"  >{$label}</label>&nbsp;{$attributes['seperator']}";
					}
				}
				$contents = str_replace( $matches[0][$key], $replace, $contents );
			}
		}
		return $contents;
	}

	function parse_literal_encode( $contents )
	{
		$patt = "/<literal>(.*)<\\/literal>/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[1][$key], base64_encode( $matches[1][$key] ), $contents );
			}
		}
		return $contents;
	}

	function parse_literal_decode( $contents )
	{
		$patt = "/<literal>(.*)<\\/literal>/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], base64_decode( $matches[1][$key] ), $contents );
			}
		}
		return $contents;
	}

}

?>
