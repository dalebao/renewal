<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/9
 * Time: 下午2:55
 */

namespace App\Utils\Customer;


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
        // TODO: Implement register() method.
        $pimple['customer'] = function ($pimple) {
            return new Customer($pimple);
        };
    }


}