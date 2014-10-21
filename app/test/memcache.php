<?php
$mem = new Memcache;
$mem->connect("127.0.0.1", 11211);
$time_start = microtime_float();
//保存数据
for($i = 0; $i < 100000; $i ++){
    $mem->set("key$i",$i,0,3);
}
$time_end = microtime_float();
$run_time = $time_end - $time_start;
echo "用时 $run_time 秒\n"

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
?>