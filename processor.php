<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 上午10:06
 */

require "vendor/autoload.php";


$serv = new swoole_server('127.0.0.1', 9501);


$serv->set([
    'worker_num' => 3,   //工作进程数量
    'debug_mode'=> 1,
//    'task_worker_num'=>8
    'daemonize' => true, //是否作为守护进程
]);

$serv->on('WorkerStart', function ($serv, $fd) {
    try {
        $container = new \App\Utils\ServiceContainer([\App\Utils\Customer\ServiceProvider::class]);
        $customer = $container->customer->getInstance()->setPid($fd);
        var_dump(date('Ymd H:i:s', time()));
        while (True) {
            $customer->exec();
        }
    } catch (Exception $e) {
        var_dump($e->getMessage());
    }
});

$serv->on('Task', function ($serv, $fd, $fromId, $data) {
    // 收到数据后发送给客户端
    echo $data;
});
$serv->on('Receive', function ($serv, $fd, $fromId, $data) {
    // 收到数据后发送给客户端
    $serv->send($fd, 'Server ' . $data);
});

$serv->on('Finish', function ($serv, $fd) {
    echo "Client close." . PHP_EOL;
});

// 客户端断开连接或者server主动关闭连接时 worker进程内调用
$serv->on('Close', function ($serv, $fd) {
    echo "Client close." . PHP_EOL;
});


$serv->start();