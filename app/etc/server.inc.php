<?php

/*** 上传服务器设置 ***/

$staticServers = array (
//
//	1 => 
//		array (
//		'server_id' => '1',
//		'server_name' => '静态服务器',
//		'server_url' => 'http://static.zq.9wee.com',
//		'server_type' => 'local',
//		'local_path' => '../archive/upload',
//		),
//	

	1 => 
		array (
		'server_id' => '1',
		'server_name' => '静态服务器',
		'server_url' => 'http://127.0.0.1',
		'server_type' => 'remote',
		'ftp_ip' => '192.168.0.8',
		'ftp_port' => '24',
		'ftp_user' => 'eks',
		'ftp_password' => '123456',
		'ftps' => true,
		),

	);

?>