<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/5
 * Time: 上午10:18
 */

namespace App\Utils\DB;

use Pimple\ServiceProviderInterface;
use Pimple\Container;

/**
 * Class ServiceProvider
 * @package App\Utils\DB
 */
class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Container $pimple
     */
    public function register(Container $pimple)
    {
        $pimple['db'] = function ($pimple) {
            return new DB($pimple);
        };
    }
}