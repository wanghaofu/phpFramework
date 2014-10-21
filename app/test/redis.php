<?php
//连接
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$time_start = microtime_float();
//保存数据
for($i = 0; $i < 100000; $i ++){
    $redis->sadd("key$i",$i);
}
    $time_end = microtime_float();
    $run_time = $time_end - $time_start;
echo "用时 $run_time 秒\n";
//关闭连接
$redis->close()
stats_dens_f(sd, dfr1, dfr2)


function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}


/*
//连接
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$time_start = microtime_float();
//保存数据
for($i = 0; $i < 100000; $i ++){
    $redis->sadd("key$i",$i);
    $redis->expire("key$i",3);
}
$time_end = microtime_float();
$run_time = $time_end - $time_start;
echo "用时 $run_time 秒\n";
//关闭连接
$redis->close();

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
*/


/*//连接
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$time_start = microtime_float();
//保存数据
for($i = 0; $i < 100000; $i ++){
　　$pipe=$redis->pipeline();
    $pipe->sadd("key$i",$i);
    $pipe->expire("key$i",3);
    $replies=$pipe->execute();
}
$time_end = microtime_float();
$run_time = $time_end - $time_start;
echo "用时 $run_time 秒\n";
//关闭连接
$redis->close();

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}*/
?>
