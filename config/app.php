<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/5
 * Time: 下午1:21
 */

return [
    'redis' => [
        'servers' => [
            "tcp://122.227.58.83:6373",
            'tcp://122.227.58.83:6374',
            'tcp://122.227.58.83:6375'
        ],
        'options' => [
            'cluster' => 'redis',
        ],
    ],
    'redis_1' => [
        'servers' => ['tcp://redis:6379'],
        'options' => []
    ],
    'mysql' => [
        'driver' => 'mysql',
        'write' => [
            'host' => '122.227.58.83',
        ],
        'read' => [
            'host' => '122.227.58.83',
        ],
        'database' => 'facilitator',
        'username' => 'root',
        'password' => 'meidi',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
        'strict' => false,
        'engine' => null,
    ],
    'rabbitmq' => [
        'link' => [
            'host' => '172.19.0.4',
            'port' => '5672',
            'login' => 'guest',
            'password' => 'guest',
            'vhost' => '/'
        ],
        'exchange_name' => 'e_linvo', //交换机名
        'queue_name' => 'q_linvo', //队列名
        'route_key' => 'key_1', //路由key
    ],


    'db_log' => [
        'name' => 'DB',
        'path' => 'logs/DB',
        'date' => true
    ],
    'info_log' => [
        'name' => 'Info',
        'path' => 'logs/Info',
        'date' => true
    ],
    'war_log' => [
        'name' => 'Warn',
        'path' => 'logs/Warn',
        'date' => true
    ],
    'redis_log' => [
        'name' => 'Redis',
        'path' => 'logs/Redis',
        'date' => true
    ]
];