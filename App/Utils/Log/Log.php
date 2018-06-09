<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/5
 * Time: 下午4:13
 */

namespace App\Utils\Log;


use App\Interfaces\ClientInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pimple\Container;

/**
 * Class Log
 * @package App\Utils\Log
 */
class Log implements ClientInterface
{
    /**
     * @var Container
     */
    private $app;
    /**
     * @var
     */
    protected static $log_instance;

    /**
     * Log constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->app = $container;
    }

    /**
     * 获取日志实例
     *
     * @param string $key
     * @return mixed
     * @throws \Exception
     */
    public function getInstance($key = '')
    {
        $key = empty($key) ? 'info' : $key;
        if (empty(self::$log_instance[$key])) {
            $config = $this->app->config->get($key . '_log');
            self::$log_instance[$key] = new Logger($config['name']);
            if ($config['date']) {
                $path = $config['path'] . date('Y-m-d', time());
            } else {
                $path = $config['path'];
            }
            $path .= '.log';
            self::$log_instance[$key]->pushHandler(new StreamHandler($path), Logger::WARNING);
        }
        return self::$log_instance[$key];
    }


}