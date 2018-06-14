<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 上午10:05
 */

require "vendor/autoload.php";

$http = new swoole_http_server("122.227.58.83", 9080);
$http->set([
//    'daemonize' => true, //是否作为守护进程
]);

$http->on('request', function ($request, $response) {
    $res = handler($request);
    $response->end(json_encode($res));
});

$http->start();

function handler($request)
{
    $action_arr = app('config')->get('action_arr');
    $data = $request->get;

    if (!isset($data['action']) || empty($data['action']) || !in_array($data['action'],$action_arr)){
        return 'action 参数不对';
    }
    $data_center = new \App\DataCenter\DataCenter();
    $data_center->setDataType($data['action']);

        return $data_center->handleSingle($data);
}