<?php
declare(encoding='UTF-8');
namespace MyProject 
{
	const CONNECT_OK = 1;
	class Connection 
	{ 
		static function start()
		{
			echo 'haha this is namespace test!';
		}
	/* ... */ 
	}
	function connect() {
			echo 'ok';
	 }
}

namespace test\sss\sss
{
 function connect()
 {
 	echo 'this is test namespace sss sss sss';
 }
}

namespace{ 
	// 全局代码
	session_start();
	$a = MyProject\connect();
	test\sss\sss\connect();
	echo MyProject\Connection::start();
}
?>