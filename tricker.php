<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 上午10:05
 */

require "vendor/autoload.php";
use App\Lib\Producer;

$config = [
    'host' => '172.19.0.4',
    'port' => '5672',
    'login' => 'guest',
    'password' => 'guest',
    'vhost' => '/'
];
$e_name = 'e_linvo'; //交换机名
$q_name = 'q_linvo'; //队列名
$k_route = 'key_1'; //路由key

$container = new \App\Utils\ServiceContainer([\App\Utils\Producer\ServiceProvider::class]);
$producer = $container->producer->getInstance();
swoole_timer_tick(200, function () use ($producer) {
    $producer->exec(rand(1,1000000));
});