<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/5
 * Time: 下午1:26
 */

namespace App\Utils\Config;

use Pimple\ServiceProviderInterface;
use Pimple\Container;

/**
 * Class ServiceProvider
 * @package App\Utils\Config
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple)
    {
        $pimple['config'] = function ($pimple) {
            return new Config($pimple);
        };
    }
}