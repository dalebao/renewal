<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/5
 * Time: 上午10:18
 */

namespace App\Utils\DB;


use App\Interfaces\ClientInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Pimple\Container;

/**
 * Class DB
 * @package App\Utils\DB
 */
class DB implements ClientInterface
{
    /**
     * @var Container
     */
    private $app;
    /**
     * @var
     */
    protected static $db_instance;

    /**
     * DB constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->app = $container;

    }

    /**
     * 获取数据库实例
     *
     * @param string $key
     * @return mixed
     */
    public function getInstance($key = '')
    {
        $key = empty($key) ? 'mysql' : $key;

        if (empty(self::$db_instance[$key]) && (!self::$db_instance[$key] instanceof Capsule)) {
            $config = $this->app->config->get($key);
            self::$db_instance[$key] = new Capsule;
            self::$db_instance[$key]->addConnection($config);
            self::$db_instance[$key]->setAsGlobal();
            self::$db_instance[$key]->bootEloquent();
        }
        return self::$db_instance[$key];
    }


}