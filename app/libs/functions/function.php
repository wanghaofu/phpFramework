<?php
class Common
{

	function getPublishResourceNum( )
	{
		global $db;
		global $table;
		$sql = "SELECT `value` as num FROM sys WHERE  `name` ='publishResourceNum'";
		$row = $db->getRow( $sql );
		return $row['num'];
	}

	function raisePublishResourceNum( $num = 1 )
	{
		global $db;
		global $table;
		$sql = "UPDATE sys SET `value`=value + {$num}  where `name`='publishResourceNum'";
		$row = $db->query( $sql );
	}

	function mkPublishResourcePath( )
	{
		$num = Common::getpublishresourcenum( );
		return Common::makeautopath( $num );
	}

	function PsnUrl2Url( &$PSN_URL )
	{

	}

	function makeAutoPath( $num )
	{
		$num = strval( $num );
		$add_zero = 8 - strlen( $num );
		$num = str_repeat( "0", $add_zero ).$num;
		$DirSecond = "h".substr( $num, 0, 3 );
		$DirFirst = "h".substr( $num, -5, 2 );
		return $DirSecond."/".$DirFirst;
	}

}

function parse_incoming( )
{
	global $_GET;
	global $_POST;
	global $HTTP_CLIENT_IP;
	global $REQUEST_METHOD;
	global $REMOTE_ADDR;
	global $HTTP_PROXY_USER;
	global $HTTP_X_FORWARDED_FOR;
	$return = array( );
	reset( $_GET );
	reset( $_POST );
	if ( is_array( $_GET ) )
	{
		foreach( $_GET as $key=>$value ){
			if ( is_array( $_GET[$key] ) )
			{
				foreach( $_GET[$key] as $key2=>$value2 ){
					$key2 = clean_key( $key2 );
//					if ( !$key2 ) continue;
					$return[$key][] = clean_value( $value2 );
				}
			} else {
				$return[$key] = clean_value( $value );
			}
		}
	}
	if ( is_array( $_POST ) )
	{
		foreach( $_POST as $key=>$value ){
			if ( is_array( $_POST[$key] ) )
			{
				foreach( $_POST[$key] as $key2=>$value2 ){
					$key2 = clean_key( $key2 );
//					if ( !$key2 ) continue;
					$return[$key][clean_key( $key2 )] = clean_value( $value2 );
				}
			} else {
				$return[$key] = clean_value( $value );
			}
		}
	}
	$addrs = array( );
	foreach ( array_reverse( explode( ",", $HTTP_X_FORWARDED_FOR ) ) as $x_f )
	{
		$x_f = trim( $x_f );
		if ( preg_match( "/^\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\$/", $x_f ) )
		{
			$addrs[] = $x_f;
		}
	}
	$addrs[] = $_SERVER['REMOTE_ADDR'];
	$addrs[] = $HTTP_PROXY_USER;
	$addrs[] = $REMOTE_ADDR;
	$return['IP_ADDRESS'] = select_var( $addrs );
	$return['IP_ADDRESS'] = preg_replace( "/^([0-9]{1,3})\\.([0-9]{1,3})\\.([0-9]{1,3})\\.([0-9]{1,3})/", "\\1.\\2.\\3.\\4", $return['IP_ADDRESS'] );
	$return['request_method'] = $_SERVER['REQUEST_METHOD'] != "" ? strtolower( $_SERVER['REQUEST_METHOD'] ) : strtolower( $REQUEST_METHOD );
	if ( isset($return['op']))
	{
		$data = explode( ";", $return['op'] );
		foreach ( $data as $key => $var )
		{
			$data1 = explode( "::", $var );
			$return["{$data1[0]}"] = $data1[1];
		}
	}
	return $return;
}

function select_var( $array )
{
	if ( !is_array( $array ) )
	{
		return -1;
	}
	ksort( $array );
	$chosen = -1;
	foreach ( $array as $k => $v )
	{
		if ( isset( $v ) )
		{
			$chosen = $v;
			break;
		}
	}
	return $chosen;
}

function clean_key( $key )
{
	if (empty($key))
	{
		return  $key;
	}
	$key = preg_replace( "/\\.\\./", "", $key );
	$key = preg_replace( "/\\_\\_(.+?)\\_\\_/", "", $key );
	$key = preg_replace( "/^([\\w\\.\\-\\_]+)\$/", "\$1", $key );
	return $key;
}

function clean_value( $val )
{
	if ( empty($val) )
	{
		return $val;
	}
	if ( get_magic_quotes_gpc( ) )
	{
		$val = stripslashes( $val );
	}
	return $val;
}


function escape($string, $esc_type = 'html')
{
	switch ($esc_type) {
		case 'html':
			return htmlspecialchars($string, ENT_QUOTES);

		case 'htmlall':
			return htmlentities($string, ENT_QUOTES);

		case 'url':
			return urlencode($string);

		case 'quotes':
			// escape unescaped single quotes
			return preg_replace("%(?<!\\\\)'%", "\\'", $string);

		case 'hex':
			// escape every character into hex
			$return = '';
			for ($x=0; $x < strlen($string); $x++) {
				$return .= '%' . bin2hex($string[$x]);
			}
			return $return;

		case 'hexentity':
			$return = '';
			for ($x=0; $x < strlen($string); $x++) {
				$return .= '&#x' . bin2hex($string[$x]) . ';';
			}
			return $return;

		case 'javascript':
			// escape quotes and backslashes and newlines
			return strtr($string, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n'));

		default:
			return $string;
	}
}
function __autoload( $class_name ) {
	$file ='./core/libs/lib_'.strtolower( substr($class_name,3) ).'.php';
	if ( file_exists ($file))
	{
		require $file ;
	}else {
		throw new Exception ( "$class_name is not define");
	}
}

function writeCache( $filename, $cacheData )
{
	$CacheFileHeader = "<?php\n//CMS cache file, DO NOT modify me!\n//Created on ";
	$CacheFileFooter = "\n?>";
	$cacheData = $CacheFileHeader.date( "F j, Y, H:i" )."\n\n".$cacheData.$CacheFileFooter;
	$handle = fopen( $filename, "w" );
	@flock( $handle, 3 );
	fwrite( $handle, $cacheData );
	return fclose( $handle );
}
//图像水印
function imageWaterMark( $groundImage, $waterPos = 0, $waterImage = "", $waterText = "", $textFont = 5, $textColor = "#FF0000" )
{
	$isWaterImage = FALSE;
	$formatMsg = "暂不支持该文件格式，请用图片处理软件将图片转换为GIF、JPG、PNG格式�?";
	if ( !empty( $waterImage ) && file_exists( $waterImage ) )
	{
		$isWaterImage = TRUE;
		$water_info = getimagesize( $waterImage );
		$water_w = $water_info[0];
		$water_h = $water_info[1];
		switch ( $water_info[2] )
		{
			case 1 :
				$water_im = imagecreatefromgif( $waterImage );
				break;
			case 2 :
				$water_im = imagecreatefromjpeg( $waterImage );
				break;
			case 3 :
				$water_im = imagecreatefrompng( $waterImage );
				break;
			default :
				$water_im = imagecreatefrompng( $waterImage );
				if( !$water_im ){
					$water_im = imagecreatefromgif( $waterImage );
				}
				if( !$water_im ){
					$water_im = imagecreatefromjpeg( $waterImage );
				}
				if( !$water_im )
				{
					exit( $formatMsg );
				}
		}
	}
	if ( !empty( $groundImage ) && file_exists( $groundImage ) )
	{
		$ground_info = getimagesize( $groundImage );
		$ground_w = $ground_info[0];
		$ground_h = $ground_info[1];
		switch ( $ground_info[2] )
		{
			case 1 :
				$ground_im = imagecreatefromgif( $groundImage );
				break;
			case 2 :
				$ground_im = imagecreatefromjpeg( $groundImage );
				break;
			case 3 :
				$ground_im = imagecreatefrompng( $groundImage );
				break;
			default :
				$ground_im = imagecreatefromjpeg($groundImage );

				if( !$ground_im ){
					$ground_im = imagecreatefromgif( $groundImage );
				}
				if( !$ground_im ){
					$ground_im = imagecreatefrompng( $groundImage );
				}
				if( !$ground_im )
				{
					exit( $formatMsg );
				}
				//	$ground_im = imagecreatefromjpeg( $groundImage );
				//				exit( $formatMsg );
		}
	}
	else
	{
		exit( "�?要加水印的图片不存在�?" );
	}
	if ( $isWaterImage )
	{
		$w = $water_w;
		$h = $water_h;
		$label = "图片�?";
	}
	else
	{
		$temp = imagettfbbox( ceil( $textFont * 2.5 ), 0, "C:/WINDOWS/fonts/cour.ttf", $waterText );
		$w = $temp[2] - $temp[6];
		$h = $temp[3] - $temp[7];
		unset( $temp );
		$label = "文字区域";
	}
	if ( $ground_w < $w || $ground_h < $h )
	{
		echo "�?要加水印的图片的长度或宽度比水印".$label."还小，无法生成水印！";
		return;
	}
	switch ( $waterPos )
	{
		case 0 :
			$posX = rand( 0, $ground_w - $w );
			$posY = rand( 0, $ground_h - $h );
			break;
		case 1 :
			$posX = 0;
			$posY = 0;
			break;
		case 2 :
			$posX = ( $ground_w - $w ) / 2;
			$posY = 0;
			break;
		case 3 :
			$posX = $ground_w - $w;
			$posY = 0;
			break;
		case 4 :
			$posX = 0;
			$posY = ( $ground_h - $h ) / 2;
			break;
		case 5 :
			$posX = ( $ground_w - $w ) / 2;
			$posY = ( $ground_h - $h ) / 2;
			break;
		case 6 :
			$posX = $ground_w - $w;
			$posY = ( $ground_h - $h ) / 2;
			break;
		case 7 :
			$posX = 0;
			$posY = $ground_h - $h;
			break;
		case 8 :
			$posX = ( $ground_w - $w ) / 2;
			$posY = $ground_h - $h;
			break;
		case 9 :
			$posX = $ground_w - $w;
			$posY = $ground_h - $h;
			break;
		default :
			$posX = rand( 0, $ground_w - $w );
			$posY = rand( 0, $ground_h - $h );
			break;
	}
	imagealphablending( $ground_im, TRUE );
	if ( $isWaterImage )
	{
		imagecopy( $ground_im, $water_im, $posX, $posY, 0, 0, $water_w, $water_h );
	}
	else
	{
		if ( !empty( $textColor ) && strlen( $textColor ) == 7 )
		{
			$R = hexdec( substr( $textColor, 1, 2 ) );
			$G = hexdec( substr( $textColor, 3, 2 ) );
			$B = hexdec( substr( $textColor, 5 ) );
		}
		else
		{
			exit( "水印文字颜色格式不正确！" );
		}
		imagestring( $ground_im, $textFont, $posX, $posY, $waterText, imagecolorallocate( $ground_im, $R, $G, $B ) );
	}
	@unlink( $groundImage );
	switch ( $ground_info[2] )
	{
		case 1 :
			imagegif( $ground_im, $groundImage );
			break;
		case 2 :
			imagejpeg( $ground_im, $groundImage );
			break;
		case 3 :
			imagepng( $ground_im, $groundImage );
			break;
		default :
			exit( $errorMsg );
	}
	if ( isset( $water_info ) )
	{
		unset( $water_info );
	}
	if ( isset( $water_im ) )
	{
		imagedestroy( $water_im );
	}
	unset( $ground_info );
	imagedestroy( $ground_im );
}

function delFile( $file )
{
	if ( file_exists( $file ) )
	{
		return unlink( $file );
	}
	else
	{
		return TRUE;
	}
}

function gb2unicode( $gb )
{
	if ( !trim( $gb ) )
	{
		return $gb;
	}
	$filename = INCLUDE_PATH."lib/encoder/gb2312.txt";
	$tmp = file( $filename );
	$codetable = array( );
	while ( list( $key, $value ) = each( $tmp ) )
	{
		$codetable[hexdec( substr( $value, 0, 6 ) )] = substr( $value, 9, 4 );
	}
	$utf = "";
	while ( $gb )
	{
		if ( 127 < ord( substr( $gb, 0, 1 ) ) )
		{
			$that = substr( $gb, 0, 2 );
			$gb = substr( $gb, 2, strlen( $gb ) );
			$utf .= "&#x".$codetable[hexdec( bin2hex( $that ) ) - 32896].";";
		}
		else
		{
			$utf .= substr( $gb, 0, 1 );
			$gb = substr( $gb, 1, strlen( $gb ) );
		}
	}
	return $utf;
}

function GBK2UTF8( $str )
{
	global $CharEncoding;
	if ( !class_exists( "Encoding" ) )
	{
		require_once( INCLUDE_PATH."lib/encoder/encoding.inc.php" );
	}
	if ( !isset( $CharEncoding ) )
	{
		$CharEncoding = new Encoding( );
	}
	$str = str_replace( "\r\n", "\n", $str );
	$sep = "\n";
	$str = str_replace( $sep, "[ilovelmm:)]", $str );
	$CharEncoding->SetGetEncoding( "GBK" );
	$CharEncoding->SetToEncoding( "UTF-8" );
	$str = $CharEncoding->EncodeString( $str );
	$str = str_replace( "[ilovelmm:)]", $sep, $str );
	return $str;
}

function GBK2BIG5( $str )
{
	global $CharEncoding;
	if ( !class_exists( "Encoding" ) )
	{
		require_once( INCLUDE_PATH."lib/encoder/encoding.inc.php" );
	}
	if ( !isset( $CharEncoding ) )
	{
		$CharEncoding = new Encoding( );
	}
	$str = str_replace( "\r\n", "\n", $str );
	$sep = "\n";
	$str = str_replace( $sep, "[ilovelmm:)]", $str );
	$CharEncoding->SetGetEncoding( "GBK" );
	$CharEncoding->SetToEncoding( "BIG5" );
	$str = $CharEncoding->EncodeString( $str );
	$str = str_replace( "[ilovelmm:)]", $sep, $str );
	return $str;
}

function BIG52GBK( $str )
{
	global $CharEncoding;
	if ( !class_exists( "Encoding" ) )
	{
		require_once( INCLUDE_PATH."lib/encoder/encoding.inc.php" );
	}
	if ( !isset( $CharEncoding ) )
	{
		$CharEncoding = new Encoding( );
	}
	$str = str_replace( "\r\n", "\n", $str );
	$sep = "\n";
	$str = str_replace( $sep, "[ilovelmm:)]", $str );
	$CharEncoding->SetGetEncoding( "GBK" );
	$CharEncoding->SetToEncoding( "BIG5" );
	$str = $CharEncoding->EncodeString( $str );
	$str = str_replace( "[ilovelmm:)]", $sep, $str );
	return $str;
}

function UTF8ToGBK( $str )
{
	global $CharEncoding;
	if ( !class_exists( "Encoding" ) )
	{
		require_once( "./include/lib/encoder/encoding.inc.php" );
	}
	if ( !isset( $CharEncoding ) )
	{
		$CharEncoding = new Encoding( );
	}
	$str = str_replace( "\r\n", "\n", $str );
	$sep = "\n";
	$str = str_replace( $sep, "[ilovelmm:)]", $str );
	$CharEncoding->SetGetEncoding( "UTF-8" );
	$CharEncoding->SetToEncoding( "GBK" );
	$str = $CharEncoding->EncodeString( $str );
	$str = str_replace( "[ilovelmm:)]", $sep, $str );
	return $str;
}


function highlight( &$content, $highlightstr, $length = 0, $color1 = "<font color=red>", $color2 = "</font>" )
{
	global $SYS_CONFIG;
	if ( substr( strtolower( $SYS_CONFIG['language'] ), 0, 4 ) == "utf8" )
	{
		return utf8_highlight( $content, $highlightstr, $length, $color1, $color2 );
	}
	else
	{
		$keywords = explode( " ", trim( fulltextseparater( $highlightstr ) ) );
		if ( $keywords[0] != "" )
		{
			$start = strpos( $content, $keywords[0] );
		}
		if ( $length != 0 )
		{
			$content = substr( $content, $start, $length );
		}
		$content = str_replace( $highlightstr, $color1.$highlightstr.$color2, $content );
		foreach ( $keywords as $key => $var )
		{
			if ( $var == $highlightstr )
			{
				continue;
			}
			$content = str_replace( $var, $color1.$var.$color2, $content );
		}
		return $content;
	}
}
function html2txt( $document )
{
	$search = array( "'<script[^>]*?>.*?</script>'si", "'<[\\/\\!]*?[^<>]*?>'si", "'([\r\n])[\\s]+'", "'&(quot|#34);'i", "'&(amp|#38);'i", "'&(lt|#60);'i", "'&(gt|#62);'i", "'&(nbsp|#160);'i", "'&(iexcl|#161);'i", "'&(cent|#162);'i", "'&(pound|#163);'i", "'&(copy|#169);'i", "'&#(\\d+);'e" );
	$replace = array(
	"",
	"",
	"",
	"\"",
	"&",
	"<",
	">",
	" ",
	chr( 161 ),
	chr( 162 ),
	chr( 163 ),
	chr( 169 ),
	"chr(\\1)"
	);
	$text = preg_replace( $search, $replace, $document );
	return $text;
}

function fulltextSeparater( $str )
{
	$strlen = strlen( $str );
	$header = 0;
	$chinese = 0;
	while ( $i < $strlen )
	{
		$ascii = ord( substr( $str, $i, 1 ) );
		if ( 160 < $ascii )
		{
			$ascii2 = ord( substr( $str, $i + 1, 1 ) );
			if ( $header )
			{
				$tmpstr .= $headerdata.substr( $str, $i, 2 )." ";
				if ( $chinese )
				{
					$headerdata = substr( $str, $i, 2 );
					$header = 1;
				}
				else
				{
					$header = 0;
				}
				++$i;
				++$i;
			}
			else
			{
				$tmpstr .= substr( $str, $i, 2 );
				if ( $chinese )
				{
					$headerdata = substr( $str, $i, 2 );
				}
				$header = 1;
				if ( !$chinese )
				{
					$chinese = 1;
				}
				++$i;
				++$i;
			}
		}
		else
		{
			$ascii2 = ord( substr( $str, $i + 1, 1 ) );
			if ( 160 < $ascii2 )
			{
				$tmpstr .= substr( $str, $i, 1 )." ";
			}
			else
			{
				$tmpstr .= substr( $str, $i, 1 );
			}
			++$i;
		}
	}
	return $tmpstr;
}

function AutoMini( $srcFile, $pixel, &$List, $_quality = 75, $_cut = 1, $_urlheader = "", $cache = TRUE, $miniMode = "1" ,$remotion=false)
{
	global $db;
	global $table;
	global $SYS_ENV;
	if ( !empty( $_urlheader ) )
	{
		$srcFile = $_urlheader.$srcFile;
	}
	/** 王涛　２００９／３／２ 转换远程路经为本地物理路经**/

	if ( !$remotion ){
		$baseurl= $_SERVER['DOCUMENT_ROOT'];
		if (preg_match ("/http/i", $srcFile))
		{
			$imgurlbase=parse_url($srcFile);
			$srcFile=trim($baseurl.$imgurlbase['path']);
		}
	}
	/*	王涛增加结束	*/
	$file = fopen( $srcFile, "r" );
	if ( !$file )
	{
		return FALSE;
	}
	else
	{
		fclose( $file );
	}
	$_quality = empty( $_quality ) ? 75 : $_quality;
	$pixelInfo = explode( "*", $pixel );
	$_type = strtolower( substr( strrchr( $srcFile, "." ), 1 ) );
	$data = getimagesize( $srcFile );
	switch ( $data[2] )
	{
		case 1 :
			if ( !function_exists( "ImageCreateFromGIF" ) )
			{
				Error::raiseerror( "func_imagecreatefromgif_does_not_exists", E_USER_WARNING );
				return FALSE;
			}
			$_im = imagecreatefromgif( $srcFile );
			break;
		case 2 :
			if ( !function_exists( "imagecreatefromjpeg" ) )
			{
				Error::raiseerror( "func_imagecreatefromjpeg_does_not_exists", E_USER_WARNING );
				return FALSE;
			}
			$_im = imagecreatefromjpeg( $srcFile );
			break;
		case 3 :
			if ( !function_exists( "ImageCreateFromPNG" ) )
			{
				Error::raiseerror( "func_imagecreatefrompng_does_not_exists", E_USER_WARNING );
				return FALSE;
			}
			$_im = imagecreatefrompng( $srcFile );
			break;
	}
	$sizeInfo['width'] = imagesx( $_im );
	$sizeInfo['height'] = imagesy( $_im );
	if ( $sizeInfo['width'] == $pixelInfo[0] && $sizeInfo['height'] == $pixelInfo[1] )
	{
		return $srcFile;
	}
	else
	{
		if ( $sizeInfo['width'] < $pixelInfo[0] && $sizeInfo['height'] < $pixelInfo[1] && $miniMode == "2" )
		{
			return $srcFile;
		}
		else
		{
			if ( empty( $List ) )
			{
				return $srcFile;
			}
			$pathInfo = pathinfo( $srcFile );
			if ( $cache )
			{
				//				2010061111070750942_552*500.jpg
			
				$searchFileName = preg_replace( "/\\.([A-Za-z0-9]*)\$/isU", "_".$pixel.".\\1", $pathInfo['basename'] );
				//				$searchFileName = preg_replace( "/\\.([A-Za-z0-9]+)\$/isU", "_".$pixelInfo[0]."*".$pixelInfo[1].".\\1", $pathInfo['basename'] );
				$sql= "select `file_name`,`URL` FROM publish_log where `file_name` LIKE '%{$searchFileName}' " ;
				$result = $db->getRow( $sql );
				if ( !empty( $result['URL'] )  && file_exists( $result['file_name'] ) )
				{
					return $result['URL'];
				}
			}
			$tmpFile = CACHE_DIR.$pathInfo['basename'];
			$resize_ratio = $pixelInfo[0] / $pixelInfo[1];
			$ratio = $sizeInfo['width'] / $sizeInfo['height'];
			if ( $_cut == 1 )
			{
				if ( $resize_ratio <= $ratio )
				{
					$newimg = imagecreatetruecolor( $pixelInfo[0], $pixelInfo[1] );
					imagecopyresampled( $newimg, $_im, 0, 0, 0, 0, $pixelInfo[0], $pixelInfo[1], $sizeInfo['height'] * $resize_ratio, $sizeInfo['height'] );
					$_result = imagejpeg( $newimg, $tmpFile, $_quality );
				}
				if ( $ratio < $resize_ratio )
				{
					$newimg = imagecreatetruecolor( $pixelInfo[0], $pixelInfo[1] );
					imagecopyresampled( $newimg, $_im, 0, 0, 0, 0, $pixelInfo[0], $pixelInfo[1], $sizeInfo['width'], $sizeInfo['width'] / $resize_ratio );
					$_result = imagejpeg( $newimg, $tmpFile, $_quality );
				}
			}
			else
			{
				if ( $resize_ratio <= $ratio )
				{
					$newimg = imagecreatetruecolor( $pixelInfo[0], $pixelInfo[0] / $ratio );
					imagecopyresampled( $newimg, $_im, 0, 0, 0, 0, $pixelInfo[0], $pixelInfo[0] / $ratio, $sizeInfo['width'], $sizeInfo['height'] );
					$_result = imagejpeg( $newimg, $tmpFile, $_quality );
				}
				if ( $ratio < $resize_ratio )
				{
					$newimg = imagecreatetruecolor( $pixelInfo[1] * $ratio, $pixelInfo[1] );
					imagecopyresampled( $newimg, $_im, 0, 0, 0, 0, $pixelInfo[1] * $ratio, $pixelInfo[1], $sizeInfo['width'], $sizeInfo['height'] );
					$_result = imagejpeg( $newimg, $tmpFile, $_quality );
				}
			}
			imagedestroy( $_im );
			imagedestroy( $newimg );
			if ( $_result )
			{
				$dataPath = Common::mkpublishresourcepath( );
				$destination = $dataPath."/".preg_replace( "/\\.([A-Za-z0-9]*)\$/isU", "_".$pixelInfo[0]."*".$pixelInfo[1].".\\1", $pathInfo['basename'] );
				$publish_path='resource/thumb';
				$publishURL ="http://tiantangwan.com/{$publish_path}"."/".$destination;
				$file_name="./{$publish_path}/{$destination}";
				if (  _upload( $tmpFile, $file_name ) )
				{
					$sql="replace into publish_log(image_id,file_name,URL) value({$List['image_id']},'{$file_name}','{$publishURL}')";
					$db->exec( $sql );
					@unlink( $tmpFile );
					Common::raisepublishresourcenum( );
					return $publishURL;
				}
			}
			else
			{
				return $srcFile;
			}
		}
	}
}
/*
function AutoMini( $srcFile, $pixel, &$List, $_quality = 75, $_cut = 1, $_urlheader = "", $cache = TRUE, $miniMode = "1" ,$remotion=false)
{
global $db;
global $table;
global $SYS_ENV;
if ( !empty( $_urlheader ) )
{
$srcFile = $_urlheader.$srcFile;
}
if ( !$remotion ){
$baseurl= $_SERVER['DOCUMENT_ROOT'];
if (preg_match ("/http/i", $srcFile))
{
$imgurlbase=parse_url($srcFile);
$srcFile=trim($baseurl.$imgurlbase['path']);
}
}
$pathInfo = pathinfo( $srcFile );
$searchFileName = preg_replace( "/\\.([A-Za-z0-9]+)\$/isU", "_".$pixel.".\\1", $pathInfo['basename'] );
if ( $cache )
{
$sql= "select `file_name`,`URL` FROM publish_log where `file_name` LIKE '%{$searchFileName}' " ;
$result = $db->getRow( $sql );

if ( !empty( $result['URL'] ) && file_exists( $result['file_name'] ) )
{

return $result['URL'];
}
}
if ( !file_exists($srcFile) )
{
return FALSE;
}
else
{
$_quality = empty( $_quality ) ? 75 : $_quality;
$pixelInfo = explode( "*", $pixel );

$_type = strtolower( substr( strrchr( $srcFile, "." ), 1 ) );
$data = getimagesize( $srcFile );

switch ( $data[2] )
{
case 1 :
if ( !function_exists( "ImageCreateFromGIF" ) )
{
Error::raiseerror( "func_imagecreatefromgif_does_not_exists", E_USER_WARNING );
return FALSE;
}
$_im = imagecreatefromgif( $srcFile );
break;
case 2 :
if ( !function_exists( "imagecreatefromjpeg" ) )
{
Error::raiseerror( "func_imagecreatefromjpeg_does_not_exists", E_USER_WARNING );
return FALSE;
}
$_im = imagecreatefromjpeg( $srcFile );
break;
case 3 :
if ( !function_exists( "ImageCreateFromPNG" ) )
{
Error::raiseerror( "func_imagecreatefrompng_does_not_exists", E_USER_WARNING );
return FALSE;
}
$_im = imagecreatefrompng( $srcFile );
break;
}
$sizeInfo['width'] = imagesx( $_im );
$sizeInfo['height'] = imagesy( $_im );
if ( $sizeInfo['width'] == $pixelInfo[0] && $sizeInfo['height'] == $pixelInfo[1] )
{
return $srcFile;
}
else
{
if ( $sizeInfo['width'] < $pixelInfo[0] && $sizeInfo['height'] < $pixelInfo[1] && $miniMode == "2" )
{
return $srcFile;
}


$tmpFile = CACHE_DIR.$pathInfo['basename']; //缓存目录缩略�?
$resize_ratio = $pixelInfo[0] / $pixelInfo[1];
$ratio = $sizeInfo['width'] / $sizeInfo['height'];
if ( $_cut == 1 )
{
if ( $resize_ratio <= $ratio )
{
$newimg = imagecreatetruecolor( $pixelInfo[0], $pixelInfo[1] );
imagecopyresampled( $newimg, $_im, 0, 0, 0, 0, $pixelInfo[0], $pixelInfo[1], $sizeInfo['height'] * $resize_ratio, $sizeInfo['height'] );
$_result = imagejpeg( $newimg, $tmpFile, $_quality );
}
if ( $ratio < $resize_ratio )
{
$newimg = imagecreatetruecolor( $pixelInfo[0], $pixelInfo[1] );
imagecopyresampled( $newimg, $_im, 0, 0, 0, 0, $pixelInfo[0], $pixelInfo[1], $sizeInfo['width'], $sizeInfo['width'] / $resize_ratio );
$_result = imagejpeg( $newimg, $tmpFile, $_quality );
}
}
else
{
if ( $resize_ratio <= $ratio )
{
$newimg = imagecreatetruecolor( $pixelInfo[0], $pixelInfo[0] / $ratio );
imagecopyresampled( $newimg, $_im, 0, 0, 0, 0, $pixelInfo[0], $pixelInfo[0] / $ratio, $sizeInfo['width'], $sizeInfo['height'] );
$_result = imagejpeg( $newimg, $tmpFile, $_quality );
}
if ( $ratio < $resize_ratio )
{
$newimg = imagecreatetruecolor( $pixelInfo[1] * $ratio, $pixelInfo[1] );
imagecopyresampled( $newimg, $_im, 0, 0, 0, 0, $pixelInfo[1] * $ratio, $pixelInfo[1], $sizeInfo['width'], $sizeInfo['height'] );
$_result = imagejpeg( $newimg, $tmpFile, $_quality );
}
}
imagedestroy( $_im );
imagedestroy( $newimg );
if ( $_result )
{
$dataPath = Common::mkpublishresourcepath( );
$destination = $dataPath."/".preg_replace( "/\\.([A-Za-z0-9]+)\$/isU", "_".$pixelInfo[0]."*".$pixelInfo[1].".\\1", $pathInfo['basename'] );
$publish_path='resource/thumb';
$publishURL ="http://tiantangwan.com/{$publish_path}"."/".$destination;
$file_name="./{$publish_path}/{$destination}";
if (  _upload( $tmpFile, $file_name ) )
{
$sql="replace into publish_log(image_id,file_name,URL) value({$List['image_id']},'{$file_name}','{$publishURL}')";
$db->exec( $sql );
@unlink( $tmpFile );
Common::raisepublishresourcenum( );
return $publishURL;
}
else
{
return false;
}
}
else
{
return $srcFile;
}
}
}
}*/


function _upload( $filename, $destination )
{
	$path = $destination;
	$pathInfo = pathinfo( $path );
	cmsware_mkdir( $pathInfo['dirname'] );
	if ( copy( $filename, $path ) )
	{
		//			$this->logIt( $destination, "binary" );
		return true;
	}
	else
	{
		return false;
	}
}
function CMSware_mkDir( $directory, $mode = 511 )
{
	global $SYS_ENV;
	global $SYS_CONFIG;
	if ( !empty( $SYS_CONFIG['dir_mode'] ) )
	{
		$mode = $SYS_CONFIG['dir_mode'];
	}
	if ( is_dir( $directory ) )
	{
		return TRUE;
	}
	if ( $SYS_CONFIG['ftp_mode'] == "1" )
	{
		//		if ( function_exists( "ftp_connect" ) )
		//		{
		//			$mode = decoct( $mode );
		//			if ( strlen( $mode ) == 4 )
		//			{
		//				$mode = substr( $mode, 1 );
		//			}
		//			$conn_id = @ftp_connect( $SYS_CONFIG['ftp_host'], $SYS_CONFIG['ftp_port'] );
		//			$login_result = @ftp_login( $conn_id, $SYS_CONFIG['ftp_username'], $SYS_CONFIG['ftp_password'] );
		//			if ( !$conn_id || !$login_result )
		//			{
		//				echo "<font color=red>FTP connection has failed!</font><br>Attempted to connect to {$ftp_server} for user {$ftp_user_name}.<br>";
		//				echo "Please reset you FTP accounts correctly in your  system setting.";
		//				exit( );
		//			}
		//			else
		//			{
		//				if ( is_dir( $directory ) )
		//				{
		//					return TRUE;
		//				}
		//				$fullpath = "";
		//				$_path = str_replace( DIRECTORY_SEPARATOR, "/", $directory );
		//				$_path = split( "/", $directory );
		//				while ( list( , $v ) = each( $_path ) )
		//				{
		//					$fullpath .= "{$v}/";
		//					$dopath = File::_ftp_realpath( $SYS_CONFIG['ftp_cms_admin_path'], $fullpath );
		//					if ( !( is_dir( $fullpath ) == FALSE ) && !ftp_mkdir( $conn_id, $dopath ) )
		//					{
		//						ftp_site( $conn_id, "CHMOD ".$mode." ".$dopath );
		//					}
		//				}
		//				return TRUE;
		//				@ftp_close( $conn_id );
		//			}
		//		}
		//		else
		//		{
		//			echo "You PHP may running in the safe mode,SYSTEM try to use ftp to creat directory .<br> but the FTP module can not found,Please contact to you web administrator to install it";
		//			return FALSE;
		//		}
	}
	else if ( is_dir( $directory ) )
	{
		return TRUE;
	}
	else if ( File::xmkdir( $directory, $mode ) )
	{
		if ( $handle = fopen( $directory."/index.html", "a" ) )
		{
			fwrite( $handle, "" );
			fclose( $handle );
		}
		return TRUE;
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
				$begin = FALSE;
			}
			else
			{
				if ( $var == ".." )
				{
					$basedir .= "../";
					$begin = FALSE;
				}
				else
				{
					if ( !$begin )
					{
						$var = $var;
						$begin = TRUE;
					}
					else
					{
						$var = "/".$var;
					}
					if ( cmsware_mkdir( $basedir.$var, $mode ) )
					{
						@chmod($basedir.$var,0777);
						$repair = TRUE;
						$basedir .= $var;
					}
					else
					{
						$repair = FALSE;
					}
				}
			}
		}
		return $repair;
	}
}
/**
 * check $var is exists and is null
 * @param unknown_type $a
 * @return boolean
 */
function checkNull($a)
{
	if(array_key_exists($a,$GLOBALS))
	{
		global $$a;
		if(is_null($$a))
			return true;
	}
	return  false;
}
function de( $str , $track=0 , $exit=false ){
	global $debugnum;
	$debugnum++;

	$debugInfo =  debug_backtrace();
	echo "<div style='font-size:14px;background-color:#f1f6f7'>";
	echo "<div style='font-size:16px;background-color:dfe5e6;color:#001eff;font-weight:bold'>";
	foreach( $debugInfo as $key=>$value ){
		if($key==0 ){
			echo "*** <span style='font-size:10px'>{$debugnum}</span><span style='font-weight:normal'> {$value['file']}</span>  <span style='font-size:20;color:red'> {$value['line']} </span>(row) </br>";
		} else {
			if ( $track )
			{
				echo "&nbsp;&nbsp;<span style='font-size:12px;'>>> include in file:{$value['file']} line:{$value['line']} row </br></span>";
			} else {
				break;
			}
		}
	}
	echo "</div>";
	echo '<pre>';
	if ( !isset( $str ) )
	{
		echo 'the vars in not set!\n\r';
	}elseif ( is_numeric($str) ){
		echo $str;
	}elseif ( is_object( $str ) ){
		print_r( $str);
	}elseif ( is_string( $str )){
		echo $str;
	}elseif( is_array( $str ) ){
		print_r( $str );
	}elseif ( is_null( $str )){
		echo 'the vars is null\n\r ';
	}elseif( is_bool( $str ) ){
		echo $str;
	}
	echo '</pre>';
	echo "</div>";
	if ( $exit ){
		exit();
	}
}
?>
