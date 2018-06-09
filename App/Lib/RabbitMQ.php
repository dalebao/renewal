<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 下午1:13
 */

namespace App\Lib;


class RabbitMQ
{
    protected static $amqpConnection;
    protected static $amqpChannel;
    protected static $amqpExchange;
    protected static $amqpQueue;

    public static function init($config)
    {
        if (empty(self::$amqpConnection) || !self::$amqpConnection instanceof \AMQPConnection) {
            self::$amqpConnection = new \AMQPConnection($config);
        }
        try {
            self::$amqpConnection->connect();
            self::$amqpChannel = new \AMQPChannel(self::$amqpConnection);
        } catch (\AMQPConnectionException $e) {
            throw new \Exception($e->getMessage());
        }

    }


}