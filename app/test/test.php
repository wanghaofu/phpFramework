<?php

// $xx = function ($num){
// 	return $num + 20;
// };

// echo $xx(10);

// $arr =array();
// class test
// {
// 	var $t = function($xx)
// 	{
// 		return 20;
// 	};
// }
// echo 'xx';
// function test()
// {
//  	echo 'hello';
// }

// $s = 3;

// // ($s == 2) ?: test();
// echo $s;
// // ($s != 2) ?: test();
// const A = 's';
// CONST B= 's';

// (A==B) ?: test();

$ss = "../../../asdf.php";
echo realpath($ss);
echo __FILE__;
echo realpath(__FILE__);
echo dirname(realpath(__FILE__));

$str ="b";
for($str=0;$str<=9;$str++)
{
	echo  ord($str);
	echo '<br>';
}
// foreach($)
// {
// echo ord($str);
// echo '<br>';
// }

// if (ord($str) == 10) 
// {
// 	echo "The first character of \$str is a line feed.\n";
// }

?>