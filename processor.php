<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 上午10:06
 */

require "vendor/autoload.php";

use App\Lib\Customer;


$serv = new swoole_server('127.0.0.1', 9501);


$serv->set([
    'worker_num' => 5,
]);

$serv->on('WorkerStart', function ($serv, $fd) {
    try {
        $container = new \App\Utils\ServiceContainer([\App\Utils\Customer\ServiceProvider::class]);
        $customer = $container->customer->getInstance()->setPid($fd);
        while (True) {
            $customer->exec();
        }
    } catch (Exception $e) {
        var_dump($e->getMessage());
    }
});


$serv->on('Receive', function ($serv, $fd, $fromId, $data) {
    // 收到数据后发送给客户端
    $serv->send($fd, 'Server ' . $data);
});

// 客户端断开连接或者server主动关闭连接时 worker进程内调用
$serv->on('Close', function ($serv, $fd) {
    echo "Client close." . PHP_EOL;
});


$serv->start();