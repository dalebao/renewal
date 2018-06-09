<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 上午10:05
 */

require "vendor/autoload.php";


$container = new \App\Utils\ServiceContainer([\App\Utils\Producer\ServiceProvider::class]);
$producer = $container->producer->getInstance();
swoole_timer_tick(200, function () use ($producer) {
    $producer->exec(rand(1,1000000));
});