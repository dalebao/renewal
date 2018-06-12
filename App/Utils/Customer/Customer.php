<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/9
 * Time: 下午2:56
 */

namespace App\Utils\Customer;


use App\DataCenter\DataHandler;
use App\Interfaces\ClientInterface;
use App\Interfaces\RabbitMQInterface;
use Pimple\Container;

class Customer implements ClientInterface, RabbitMQInterface
{
    private $app;
    private $fd;
    protected static $amqpConnection;
    protected static $amqpChannel;
    protected static $amqpExchange;
    protected static $amqpQueue;
    protected static $instance;
    protected static $customer;

    /**
     * Customer constructor.
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
        $key = empty($key) ? 'rabbitmq' : $key;
        if (empty(self::$customer[$key])) {
            $config = $this->app->config->get($key);
            $link = $config['link'];
            $exchange_name = $config['exchange_name'];
            $queue_name = $config['queue_name'];
            $route_key = $config['route_key'];
            self::$customer[$key] = self::init($link)->setExchange($exchange_name)->setQueue($queue_name)->bind($route_key);
        }
        return self::$customer[$key];
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
        self::$amqpExchange = new \AMQPExchange(self::$amqpChannel);
        self::$amqpExchange->setName($exchange_name);
        self::$amqpExchange->setType(AMQP_EX_TYPE_DIRECT); //direct类型
        self::$amqpExchange->setFlags(AMQP_DURABLE); //持久化
//        var_dump("Exchange Status:" . self::$amqpExchange->declare() . "\n");
        return $this;
    }

    /**
     * @param $queue_name
     * @return $this
     * @throws \AMQPConnectionException
     * @throws \AMQPQueueException
     */
    public function setQueue($queue_name)
    {
        //创建队列
        self::$amqpQueue = new \AMQPQueue(self::$amqpChannel);
        self::$amqpQueue->setName($queue_name);

        self::$amqpQueue->setFlags(AMQP_DURABLE); //持久化
//        echo("Message Total:" . self::$amqpQueue->declare());
        //绑定交换机与队列，并指定路由键
        return $this;
    }

    /**
     * @param $route_key
     * @return $this|mixed
     */
    public function bind($route_key)
    {
        self::$amqpQueue->bind(self::$amqpExchange->getName(), $route_key);
        return $this;
    }

    public function setPid($fd){
        $this->fd = $fd;
        return $this;
    }

    /**
     * @param String $message
     * @return mixed
     */
    public function exec(String $message = '')
    {
        $data_handler = new DataHandler();
        self::$amqpQueue->consume(function ($envelope, $queue) use ($data_handler){
            $msg = $envelope->getBody();
            echo $msg . "\n"; //处理消息
            $info = json_decode($msg,true);
            $data_handler->handleData($info,$this->fd);
            $queue->ack($envelope->getDeliveryTag()); //手动发送ACK应答
        });
    }

}