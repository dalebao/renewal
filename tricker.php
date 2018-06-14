<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 上午10:05
 */

require "vendor/autoload.php";


//$data_center = new \App\DataCenter\DataCenter();
//$data_center->setDataType('renewal');//
//swoole_timer_tick(5 * 60 * 1000, function ($a) use ($data_center) {
//    var_dump($a);
//    $data_center->test();
//});

//$data_center->setDataType('refund');
//swoole_timer_tick(4*60*60*1000, function () use ($data_center) {
//    $data_center->test();
//});


$serv = new swoole_server('127.0.0.1', 9601, SWOOLE_BASE, SWOOLE_SOCK_TCP);
$serv->set([
//    'daemonize' => true,
    'worker_num'=>1
]);

$serv->on('WorkerStart','workStart');

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

function workStart($serv,$fd)
{
    $datacenter = new \App\DataCenter\DataCenter();
    $datacenter->setDataType('renewal');
    //自动续费
    $serv->tick(10000, function ($id) use($datacenter){
//        $datacenter->test();
        var_dump(222,$id);
    });

    $serv->tick(1000,function (){
        var_dump(1);
    });
}
