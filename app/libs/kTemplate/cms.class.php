<?php
class CMS
{
	function TPL_PULL_Parser( &$tpl_source )
	{
		$patt = "/<CMS::([\\S]+):([\\S]+)[\\s]+(.*)>(.*)<\\/CMS>/siU";
		if ( preg_match_all( $patt, $tpl_source, $match ) )
		{
			foreach ( $match[0] as $key => $var )
			{
				$params = CMS::tpl_pull_parser_parseparameter( trim( $match[3][$key] ) );
				$html = CMS::tpl_pull_parser_parsehtml( $match[4][$key] );
				$params['where'] = CMS::parse_where( $html );
				$paramesStr = CMS::vars_export( $params );
				$paramesStr = CMS::parse_params_var( $paramesStr );
				$replace = "<?php\r\n global \$PageInfo,\$params; \r\n \$params = {$paramesStr};\r\n\$this->_tpl_vars['{$match[2][$key]}'] = CMS_{$match[1][$key]}(\$params); \r\n    \$this->_tpl_vars['PageInfo'] = &\$PageInfo;  \r\n?>".$html;
				$tpl_source = str_replace( $match[0][$key], $replace, $tpl_source );
			}
		}
		$tpl_source = CMS::parse_ssi( $tpl_source );
		$tpl_source = CMS::parse_cms( $tpl_source );
		$tpl_source = CMS::parse_block( $tpl_source );
		$tpl_source = CMS::tpl_pull_parser_cmsware3( $tpl_source );
		return $tpl_source;
	}

	function TPL_PULL_Parser_cmsware3( &$tpl_source )
	{
		$patt = "/<CMS[\\s]+([^\n]*)[\\/]>/is";
		if ( preg_match_all( $patt, $tpl_source, $match ) )
		{
			foreach ( $match[0] as $key => $var )
			{
				$params = CMS::tpl_pull_parser_parseparameter_cmsware3( trim( $match[1][$key] ) );
				$include_tpl = "";
				if ( isset( $params['tpl'] ) )
				{
					$include_tpl = "\r\n<include file=\"".$params['tpl']."\" />\r\n";
					unset( $params['tpl'] );
				}
				$paramesStr = CMS::vars_export( $params );
				$replace = "<?php\r\n global \$PageInfo,\$params; \r\n \$params = {$paramesStr};\r\n\$this->_tpl_vars['{$params['return']}'] = CMS_{$params['action']}(\$params); \r\n    \$this->_tpl_vars['PageInfo'] = &\$PageInfo;  \r\n?>".$include_tpl;
				$tpl_source = str_replace( $match[0][$key], $replace, $tpl_source );
			}
		}
		return $tpl_source;
	}

	function vars_export( $params )
	{
		$return = "array ( \r\n";
		foreach ( $params as $key => $var )
		{
			$return .= "\t'{$key}' => \"{$var}\",\r\n";
		}
		$return .= " ); \r\n";
		return $return;
	}

	function parse_cms( &$contents )
	{
		$patt = "/<CMS::([\\S]+):([\\S]+)[\\s]+(.*)>/siU";
		if ( preg_match_all( $patt, $contents, $match ) )
		{
			foreach ( $match[0] as $key => $var )
			{
				$params = CMS::tpl_pull_parser_parseparameter( trim( $match[3][$key] ) );
				$paramesStr = var_export( $params, TRUE );
				$paramesStr = CMS::parse_params_var( $paramesStr );
				$replace = "<?php\n global \$PageInfo,\$params; \n \$params = {$paramesStr};\n\$this->_tpl_vars['{$match[2][$key]}'] = CMS_{$match[1][$key]}(\$params); \n    \$this->_tpl_vars['PageInfo'] = &\$PageInfo;  \n?>".$html;
				$contents = str_replace( $match[0][$key], $replace, $contents );
			}
		}
		$search = array( "'</cms>'si" );
		$replace = array( "" );
		$contents = preg_replace( $search, $replace, $contents );
		return $contents;
	}

	function parse_params_var( $paramesStr )
	{
		$search = array( "/\\'\\{(.*)\\}\\'/si" );
		$replace = array( "\"{\\1}\"" );
		$paramesStr = preg_replace( $search, $replace, $paramesStr );
		return $paramesStr;
	}

	function TPL_PULL_Parser_parseHtml( $html )
	{
		return $html;
	}

	function parse_block( &$contents )
	{
		if ( preg_match_all( "/\\[cms-block-container:(.*)\\]/isU", $contents, $match ) )
		{
			foreach ( $match[0] as $key => $var )
			{
				$replace = "<?php \r\n ";
				$replace .= "\$type='".$match[1][$key]."';\r\n";
				$replace .= "\$tplmark= isset(\$tplmark) ? \$tplmark : basename(\$this->template_name);\r\n";
				$replace .= "include(INCLUDE_PATH.\"block.php\");\r\n";
				$replace .= "?>\n";
				$contents = str_replace( $match[0][$key], $replace, $contents );
			}
		}
		return $contents;
	}

	function parse_block__bak( &$contents )
	{
		if ( isset( $PHP_SELF ) )
		{
			$GLOBALS['GLOBALS']['_SERVER']['PHP_SELF'] = $PHP_SELF;
		}
		$info = pathinfo( $_SERVER['PHP_SELF'] );
		if ( $_SERVER['SERVER_PORT'] != 80 )
		{
			$CMSWARE_URL = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].str_replace( "\\", "/", dirname( $info['dirname'] ) )."/";
		}
		else
		{
			$CMSWARE_URL = "http://".$_SERVER['SERVER_NAME'].str_replace( "\\", "/", dirname( $info['dirname'] ) )."/";
		}
		if ( preg_match_all( "/\\[cms-block-container:(.*)\\]/isU", $contents, $match ) )
		{
			foreach ( $match[0] as $key => $var )
			{
				$replace = "<?php include(\"".$CMSWARE_URL."admin/block.php?sId=\".\$GLOBALS[IN][sId].\"&NodeID=\".\$GLOBALS[IN][NodeID].\"&type=".$match[1][$key]."&tpl=\".basename(\$this->template_name)); ?>";
				$contents = str_replace( $match[0][$key], $replace, $contents );
			}
		}
		return $contents;
	}

	function parse_ssi( &$contents )
	{
		$patt = "/\\<\\!--\\[(.*)\\]--\\>/siU";
		if ( preg_match_all( $patt, $contents, $match ) )
		{
			foreach ( $match[0] as $key => $var )
			{
				$replace = "<?php\n CMSware::cms_".$match[1][$key]."?>";
				$contents = str_replace( $match[0][$key], $replace, $contents );
			}
		}
		return $contents;
	}

	function parse_tag( &$contents )
	{
		$patt = "/".preg_quote( "[" )."@([\\S^(]+)\\(([^)]+)\\)".preg_quote( "]" )."/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], CMS::parse_tag_func_format( $var, $matches[2][$key] ), $contents );
			}
		}
		$patt = "/\\[\\\$([a-zA-Z0-9_\\.]+)\\]/siU";
		if ( preg_match_all( $patt, $contents, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$contents = str_replace( $matches[0][$key], CMS::parse_tag_format_var_display( $var ), $contents );
			}
		}
	}

	function parse_tag_format_var_display( $string )
	{
		$header = "<?php echo \$";
		if ( strpos( $string, "." ) )
		{
			$data = explode( ".", $string );
			$string = "";
			foreach ( $data as $key => $var )
			{
				if ( $key == 0 )
				{
					$string = $var;
				}
				else
				{
					$string .= "['".$var."']";
				}
			}
			$string = $header.$string.";?>";
		}
		else
		{
			$string = $header.$string.";?>";
		}
		return $string;
	}

	function parse_tag_format_var( $string )
	{
		$header = "\$";
		if ( strpos( $string, "." ) )
		{
			$data = explode( ".", $string );
			$string = "";
			foreach ( $data as $key => $var )
			{
				if ( $key == 0 )
				{
					$string = $var;
				}
				else
				{
					$string .= "['".$var."']";
				}
			}
			$string = $header.$string;
		}
		else
		{
			$string = $header.$string;
		}
		return $string;
	}

	function parse_var_format( $string )
	{
		$patt = "/\\\$([a-zA-Z0-9_\\.]+)/si";
		if ( preg_match_all( $patt, $string, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$string = str_replace( $matches[0][$key], CMS::parse_tag_format_var( $var ), $string );
			}
		}
		return $string;
	}

	function parse_tag_func_format( $funName, $params )
	{
		$header = "<?php echo ";
		$patt = "/\\\$([a-zA-Z0-9_\\.]+)/si";
		if ( preg_match_all( $patt, $params, $matches ) )
		{
			foreach ( $matches[1] as $key => $var )
			{
				$params = str_replace( $matches[0][$key], CMS::parse_tag_format_var( $var ), $params );
			}
		}
		$string = $header.$funName."(".$params.");?>";
		return $string;
	}

	function parse_if( &$contents )
	{
		$search = array( "'<if[\\s]+([^\n]+)>'si", "'<elseif[\\s]+([^\n]+)>'si", "'<else>'siU", "'</if>'siU" );
		$replace = array( "<?php if( \\1 ): ?>", "<?php elseif( \\1 ): ?>", "<?php else: ?>", "<?php endif;?>" );
		$contents = preg_replace( $search, $replace, $contents );
	}

	function parse_where( &$contents )
	{
		$patt = "/\\<where:(.*)\\>/siU";
		if ( preg_match( $patt, $contents, $match ) )
		{
			$contents = str_replace( $match[0], "", $contents );
		}
		return $match[1];
	}

	function parse_loop( &$contents )
	{
		$search = array( "'<loop[\\s]+([\\S]+)[\\s]+var=([a-zA-Z0-9_]+)[\\s]*>'siU", "'<loop[\\s]+([\\S]+)[\\s]+key=([a-zA-Z0-9_]+)[\\s]+var=([a-zA-Z0-9_]+)[\\s]*>'siU", "'<loop[\\s]+([\\S]+)[\\s]+var=([a-zA-Z0-9_]+)[\\s]+key=([a-zA-Z0-9_]+)[\\s]*>'siU", "'</loop>'siU" );
		$replace = array( "<?php if(!empty(\\1)): \n foreach (\\1 as  \$\\2): ?>", "<?php if(!empty(\\1)): \n foreach (\\1 as  \$\\2=>\$\\3): ?>", "<?php if(!empty(\\1)): \n foreach (\\1 as  \$\\3=>\$\\2): ?>", "<?php endforeach; endif;?>" );
		$contents = preg_replace( $search, $replace, $contents );
	}

	function TPL_PULL_Parser_parseParameter( $Parameter )
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

	function TPL_PULL_Parser_parseParameter_cmsware3( $Parameter )
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

}

function CMS_Parser( &$content )
{
	return CMS::tpl_pull_parser( $content );
}

?>
