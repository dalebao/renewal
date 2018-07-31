<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/9
 * Time: 下午2:57
 */

namespace App\Utils\Producer;


use App\Interfaces\ClientInterface;
use App\Interfaces\RabbitMQInterface;
use Pimple\Container;

class Producer implements ClientInterface, RabbitMQInterface
{
    private $app;
    private static $instance;
    protected static $amqpConnection;
    protected static $amqpChannel;
    protected static $amqpExchange;
    protected static $amqpQueue;
    protected static $route_key;
    protected static $producer;

    /**
     * Producer constructor.
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
        self::$instance = $this;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws \AMQPConnectionException
     */
    public function getInstance($key = '')
    {
        // TODO: Implement getInstance() method.
        $key = empty($key) ? 'rabbitmq' : $key;
        if (empty(self::$producer[$key])) {
            $config = $this->app->config->get($key);
            $link = $config['link'];
            $exchange_name = $config['exchange_name'];
            $route_key = $config['route_key'];

            self::$producer[$key] = self::init($link)->setExchange($exchange_name)->bind($route_key);
        }
        return self::$producer[$key];
    }

    /**
     * @param $link
     * @return mixed
     * @throws \AMQPConnectionException
     */
    public static function init($link)
    {
        // TODO: Implement init() method.
        if (empty(self::$amqpConnection) || !self::$amqpConnection instanceof \AMQPConnection) {
            self::$amqpConnection = new \AMQPConnection($link);
        }
        self::$amqpConnection->connect();
        self::$amqpChannel = new \AMQPChannel(self::$amqpConnection);
        return self::$instance;
    }

    /**
     * @param $exchange_name
     * @return $this|mixed
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public function setExchange($exchange_name)
    {
        // TODO: Implement setExchange() method.
        self::$amqpExchange = new \AMQPExchange(self::$amqpChannel);
        self::$amqpExchange->setName($exchange_name);
        return $this;
    }

    /**
     * @param $route_key
     * @return $this|mixed
     */
    public function bind($route_key)
    {
        self::$route_key = $route_key;
        return $this;
    }

    /**
     * @param String $message
     * @return mixed|void
     */
    public function exec(String $message = '')
    {
        // TODO: Implement exec() method.
        echo date('Y-m-d H:i:s',time()) . PHP_EOL;
        self::$amqpExchange->publish($message, self::$route_key);
    }


}