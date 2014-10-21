<?php
/****** memcache 配置 ******/

// 同步控制 memcache
$syncMcConfig = array (
	'host' => '127.0.0.1',
	'port' => 11211,
	'expire' => 86400,
	);

// 数据存储 memcache
$storeMcConfig = array (
	'host' => '127.0.0.1',
	'port' => 11211,
	);

?>