<?php
namespace Foo\Bar;
include 'file1.php';

const FOO = 2;
function foo() {}
class foo
{
    static function staticmethod() {}
}

/* 非限定名称 */
foo(); // 解析为 Foo\Bar\foo resolves to function Foo\Bar\foo
foo::staticmethod(); // 解析为类 Foo\Bar\foo的静态方法staticmethod。resolves to class Foo\Bar\foo, method staticmethod
echo FOO; // resolves to constant Foo\Bar\FOO

/* 限定名称 */
   subnamespace\foo(); // 解析为函数 Foo\Bar\subnamespace\foo
   subnamespace\foo::staticmethod(); // 解析为类 Foo\Bar\subnamespace\foo,
                                  // 以及类的方法 staticmethod
echo subnamespace\FOO; // 解析为常量 Foo\Bar\subnamespace\FOO
echo subnamespace\COUNTRY;                                  
/* 完全限定名称 */
\Foo\Bar\foo(); // 解析为函数 Foo\Bar\foo
\Foo\Bar\foo::staticmethod(); // 解析为类 Foo\Bar\foo, 以及类的方法 staticmethod
echo \Foo\Bar\subnamespace\COUNTRY.'23';
echo \Foo\Bar\FOO; // 解析为常量 Foo\Bar\FOO


namespace Foo;

function strlen() {echo 'haha';}
const INI_ALL = 3;
class Exception {}


stats_dens_f(stats_dens_f(

	, dfr1, dfr2), dfr1, dfr2);
wddx_packet_end(
	)
web= sdf;
error

$a = \strlen('hi'); // 调用全局函数strlen
$b = \INI_ALL; // 访问全局常量 INI_ALL
$c = new \Exception('error'); // 实例化全局类 Exception
print_r($c);
?>
