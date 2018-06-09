<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/5
 * Time: 上午10:17
 */

namespace App\Utils\Redis;


use Pimple\ServiceProviderInterface;
use Pimple\Container;

/**
 * Class ServiceProvider
 * @package App\Utils\Redis
 */
class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Container $pimple
     */
    public function register(Container $pimple)
    {
        $pimple['redis'] = function ($pimple) {
            return new Redis($pimple);
        };
    }
}