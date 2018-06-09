<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/5
 * Time: 上午10:17
 */

namespace App\Utils\Redis;


use App\Interfaces\ClientInterface;
use Pimple\Container;
use Predis\Client;

/**
 * Class Redis
 * @package App\Utils\Redis
 */
class Redis implements ClientInterface
{
    /**
     * @var Container
     */
    protected $app;

    /**
     * @var
     */
    protected static $redis_instance;

    /**
     * Redis constructor.
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }


    /**
     * 获取Redis实例
     * @param string $key
     * @return mixed
     */
    public function getInstance($key = '')
    {
        $key = empty($key) ? 'redis' : $key;
        if (empty(self::$redis_instance[$key])) {
            $config = $this->app->config->get($key);
            $server = $config['servers'];
            $option = isset($config['options']) ? $config['options'] : [];
            self::$redis_instance[$key] = new Client($server,$option);
        }
        return self::$redis_instance[$key];
    }


}