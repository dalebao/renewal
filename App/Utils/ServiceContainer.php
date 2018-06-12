<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/5
 * Time: 上午10:17
 */

namespace App\Utils;


use Pimple\Container;

/**
 * Class ServiceContainer
 * @property \App\Utils\Config\Config $config
 * @property \App\Utils\DB\DB $db
 * @property \App\Utils\Redis\Redis $redis
 * @property \App\Utils\Log\Log $log
 * @property \App\Utils\Customer\Customer $customer
 * @property \App\Utils\Producer\Producer $producer
 * @package App\Utils
 */
class ServiceContainer extends Container
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $defaultConfig = [];

    /**
     * @var array
     */
    protected $userConfig = [];

    /**
     * Constructor.
     *
     * @param array $providers
     * @param array $config
     * @param array $prepends
     * @param string|null $id
     */
    public function __construct(array $providers = [], array $config = [], array $prepends = [], string $id = null)
    {
        $this->providers = $providers;

        $this->registerProviders($this->getProviders());

        parent::__construct($prepends);

        $this->userConfig = $config;

        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id ?? $this->id = md5(json_encode($this->userConfig));
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $base = [
            // http://docs.guzzlephp.org/en/stable/request-options.html
//            'http' => [
//                'timeout' => 5.0,
//                'base_uri' => 'https://api.weixin.qq.com/',
//            ],
        ];

        return array_replace_recursive($base, $this->defaultConfig, $this->userConfig);
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        return array_merge([
            \App\Utils\DB\ServiceProvider::class,
            \App\Utils\Redis\ServiceProvider::class,
            \App\Utils\Config\ServiceProvider::class,
            \App\Utils\Log\ServiceProvider::class,
            \App\Utils\Customer\ServiceProvider::class,
            \App\Utils\Producer\ServiceProvider::class,
        ], $this->providers);
    }

    /**
     * Magic get access.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Magic set access.
     *
     * @param string $id
     * @param mixed $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * @param array $providers
     */
    public function registerProviders(array $providers)
    {
        foreach ($providers as $provider) {
            parent::register(new $provider());
        }
    }
}