<?php

// (c) Vladimir Fedorkov https://t.me/downtime_bar
error_reporting(E_ALL & ~E_WARNING);

// for SELECT
$mysql_host = $argv[1];
$mysql_port = $argv[2];
$mysql_user = $argv[3];
$mysql_pass = $argv[4];
$mysql_db = 'performance_schema';

### setting default 
$count = empty($argv[5]) ? 100000 : $argv[5];

if ( empty($mysql_host) ||  empty($mysql_port) ||  empty($mysql_user) ||  empty($mysql_pass))
{
    print "USAGE: php -q $argv[0] <mysql_host> <mysql_port> <mysql_user> <mysql_pass> [count]\n";
    exit;
}

// We store each request time, so be carefull with memory consumption
$timings = array();

$time_overall = 0;


function print_pct($timings)
{
    sort($timings);
    // please note that math is far from being prefect but it works
    foreach( [100, 99.999, 99.995, 99.99, 99.9, 99, 95, 80, 50, 1] as $pct)
    {
        $pos = ceil($i * (float)$pct / 100) - 1;
        echo "$pct,\t$pos,\t{$timings[$pos]}\n";
    }
}

// We user single connection to avoid additional overheads
$mysql_conn = mysqli_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db, $mysql_port) or die("Can't connect to MySQL, please check connection parameters");
$test_start = microtime(true);
for ($i = 0; $i < $count; $i++)
{
    // fancy output helps with really long tests
    if (($i % 10000) == 0) print ".";
    $start = microtime(true);
    $r = mysqli_query($mysql_conn, "SELECT 1");
    $a = mysqli_fetch_array($r);
    $stop = microtime(true);
    $diff = $stop - $start;
    $timings[] = $diff;
    $time_overall += $diff;
    // It's convenent to see current results during really long (10M+) tests
    if (($i != 0 ) && ($i % 100000) == 0)
    {
	    print "Digest for itteration {$i}: \n";
	    print_pct($timings);
    }
}
$test_stop = microtime(true);

$test_time = number_format($test_stop - $test_start, 6);
$avg_latency = number_format($time_overall / $count, 6);
$overhead_time = number_format($test_time - $time_overall, 6);
$time_overall = number_format($time_overall, 6);
print "Host: {$mysql_host}, queries: {$count} AVG latency (seconds): {$avg_latency}\n";
print "Test time: {$test_time}, run time: {$time_overall}, overhead: {$overhead_time}\n";

sort($timings);
print_pct($timings);

?>

