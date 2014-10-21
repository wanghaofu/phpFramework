<?php
define ( 'IN_SYSTEM', 1 );
/*
ini_set ('log_errors', 1);
ini_set ('display_errors', 0);
$logFile = "../logs/phplog";

if ( file_exists($logFile) && filesize($logFile) > 10000000 ) {
        rename( $logFile, "{$logFile}.old" );
}
ini_set ( 'error_log', $logFile );
error_reporting ( 55 );
*/

require_once ('../libs/global.php');

//web使用cookie实现session
$sessHandler = new stra_Core_Session_Handler_Cookie ();

stra_Core_Session::start ( $sessHandler );
$session = stra_Core_Session::getCurSession ();

//停服
$config = stra_Core_Config_Factory::create ( 'config' );


if (isset ( $_GET ['_fpath'] )) {
	$session->save ( '_fpath', $_GET ['_fpath'] );
}

if (isset ( $_GET ['_gpath'] )) {
	$session->save ( '_gpath', $_GET ['_gpath'] );
}

$panguApp = stra_Core_App::getCurApp ();

$in = new Web_Client_Request ( $_REQUEST );
$out = $panguApp->run ( $in );
$reponse = $out->castTo ( 'Web_Client_Response' );
$reponse->send ();

 