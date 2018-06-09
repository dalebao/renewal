<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/5
 * Time: 下午1:25
 */

namespace App\Utils\Config;


use App\Interfaces\ClientInterface;
use Pimple\Container;

/**
 * Class Config
 * @package App\Utils\Config
 */
class Config implements ClientInterface
{
    /**
     * @var Container
     */
    private $app;
    /**
     * @var
     */
    private $config;

    /**
     * Config constructor.
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->getInstance();
    }

    /**
     * 获取config实例
     * @param string $key
     * @return $this|mixed
     */
    public function getInstance($key = '')
    {
        $key = empty($key) ? 'app' : $key;
        $this->config = require(dirname(dirname(dirname(__DIR__))) . '/config/' . $key . '.php');
        return $this;
    }

    /**
     * 获取配置信息
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->config[$key];
    }


}