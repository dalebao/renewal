<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/9
 * Time: 下午2:56
 */

namespace App\Utils\Producer;


use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Container $pimple
     * @return mixed
     */
    public function register(Container $pimple)
    {
        $pimple['producer'] = function ($pimple) {
            return new Producer($pimple);
        };
    }
}