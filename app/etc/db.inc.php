<?php
/**
 * 警告 非分库应用dsn不要配置 成 数组 形式 
 * @var unknown_type
 */
$dbconfig = array ();

##主库 配置
$dbConfig ['stra'] = array (
		'dsn' => 'mysql:host=127.0.0.1;port=3306;',
		'user' => 'stradev',
		'password' => 'stradev',
		'database' => 'stra',
		'charset' => 'utf8',
);

##日志配置
$dbConfig ['log'] = array (
		'dsn' => 'mysql:host=127.0.0.1;port=3306;',
		'user' => 'stra',
		'password' => 'stradev',
		'database' => 'stra_logs',
		'charset' => 'utf8',
);
## gm 配置
$dbConfig ['gm'] = array (
		'dsn' => "mysql:host=127.0.0.1;port=3306;", // 数据库连接字符串
		'database' => "stra_gm", // 数据库
		'user' => "stradev", // 登陆用户
		'password' => "stradev", // 登陆密码
		'charset' => "utf8",
);


##用户 库配置
$dbConfig ['user'] = array (
		'dsn' => array (
				0=>"mysql:host=127.0.0.1;port=3306;",
				1=>"mysql:host=127.0.0.1;port=3306;",
				2=>"mysql:host=127.0.0.1;port=3306;",
				3=>"mysql:host=127.0.0.1;port=3306;",
				4=>"mysql:host=127.0.0.1;port=3306;",
				5=>"mysql:host=127.0.0.1;port=3306;",
				6=>"mysql:host=127.0.0.1;port=3306;",    // 非分库应用不要配置 成 数组 形式 
		), // 数据库连接字符串
		'dbIdx' => "0:0,1:0,2:0,3:0,4:0,5:0", // x:x 第一位表示数据库扩展索引 ，第二个表示上边配置的服务器索引 警告 非分库应用不要配置 成 数组 形式 
		'database' => 'stra', // 数据库
		'user' => "stradev", // 登陆用户
		'password' => "stradev", // 登陆密码
		'charset' => "utf8",
	
);

## 索引库配置
$dbConfig ['stra_index'] = array (
		'dsn' =>"mysql:host=127.0.0.1;port=3306;", // 数据库连接字符串
		'database' => 'stra_index', // 数据库
		'user' => "stradev", // 登陆用户
		'password' => "stradev", // 登陆密码
		'charset' => "utf8",
		
);

$dbConfig ['strategy'] = array (
		'dsn' =>"mysql:host=127.0.0.1;port=3306;", // 数据库连接字符串
		'database' => 'strategy', // 数据库
		'user' => "stradev", // 登陆用户
		'password' => "stradev", // 登陆密码
		'charset' => "utf8",
		
);

## 数据切分算法 do not modify underline


$dbConfig['user']['data_split'] = array (
		'db_split_call_fun' => 'Split::userDb',
		'table_split_call_fun' => array (
				'default' =>'Split::userTable' , // waring				
		)
);

$dbConfig ['stra_index'] ['data_split'] = array (
// 				'db_split_call_fun' => 'Split::setDbIdx', //通过 回调 方法确认是否 切分 如果 配置的 方法 为空 或者 不做处理则不切人 否则 进行切分
				'table_split_call_fun' => array (
						'uuid' =>'Split::uuid' ,
						'puuid' =>'Split::puuid' 
						) 
		); 

dbConfig::setDbConfigs($dbConfig);

?>