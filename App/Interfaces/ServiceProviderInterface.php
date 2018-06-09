<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/5
 * Time: 上午10:38
 */

namespace App\Interfaces;


use Pimple\Container;

/**
 * Interface ServiceProviderInterface
 * @package App\Interfaces
 */
interface ServiceProviderInterface
{
    /**
     * @param Container $pimple
     * @return mixed
     */
    public function register(Container $pimple);

}