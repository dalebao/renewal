<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 上午10:06
 */

require "vendor/autoload.php";

/**
 * 创建多进程
 */
$worker_num         = 6;        // 默认进程数
$workers             = [];        // 进程保存
$redirect_stdout    = false;    // 重定向输出  ; 这个参数用途等会我们看效果
for($i = 0; $i < $worker_num; $i++){
    $process = new swoole_process('callback_function', $redirect_stdout);
    // 启用消息队列 int $msgkey = 0, int $mode = 2
    $process->useQueue(0, 2);
    $pid = $process->start();
    // 管道写入内容
    $process->write('index:'.$i);

    $process->push('进程的消息队列内容');
    // 将每一个进程的句柄存起来
    $workers[$pid] = $process;
}
//
//
///**
// * 子进程回调
// * @param  swoole_process $worker [description]
// * @return [type]                 [description]
// */
function callback_function(swoole_process $worker)
{
    $recv = $worker->pop();
    echo "子输出主内容: {$recv}".PHP_EOL;
    //get guandao content
    $recv = $worker->read();
    var_dump($recv);
    $result = doTask($worker->pid);

    echo PHP_EOL.$result.'==='.$worker->pid.'==='.$recv;

//    $worker->exit(0);
}
//

/**
 * 监控/回收子进程
 */
while(1){
    $ret = swoole_process::wait();
    var_dump($ret);
    if ($ret){// $ret 是个数组 code是进程退出状态码，
        $pid = $ret['pid'];
        echo PHP_EOL."Worker Exit, PID=" . $pid . PHP_EOL;
    }else{
        break;
    }
}


/**
 * @param $fd
 * @return bool
 * @throws AMQPConnectionException
 */
function doTask($fd)
{
    $container = new \App\Utils\ServiceContainer([\App\Utils\Customer\ServiceProvider::class]);
    $customer = $container->customer->getInstance()->setPid($fd);
    while (True) {
        $customer->exec();
    }
    return true;
}
