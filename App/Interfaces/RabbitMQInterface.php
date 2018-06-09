<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 上午11:32
 */

namespace App\Interfaces;


/**
 * Interface RabbitMQInterface
 * @package App\Interfaces
 */
interface RabbitMQInterface
{
    /**
     * @param $config
     * @return mixed
     */
    public static function init($link);

    /**
     * @param $exchange_name
     * @return mixed
     */
    public function setExchange($exchange_name);

    /**
     * @param $route_key
     * @return mixed
     */
    public function bind($route_key);

    /**
     * @param array $message
     * @return mixed
     */
    public function exec(String $message);
}