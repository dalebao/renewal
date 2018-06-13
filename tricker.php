<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 上午10:05
 */

require "vendor/autoload.php";


$data_center = new \App\DataCenter\DataCenter();
$data_center->setDataType('renewal');//
swoole_timer_tick(5 * 60 * 1000, function ($a) use ($data_center) {
    var_dump($a);
    $data_center->test();
});

//$data_center->setDataType('refund');
//swoole_timer_tick(4*60*60*1000, function () use ($data_center) {
//    $data_center->test();
//});