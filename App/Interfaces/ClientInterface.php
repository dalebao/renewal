<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/5
 * Time: 下午4:47
 */

namespace App\Interfaces;
use Pimple\Container;


/**
 * Interface ClientInterface
 * @package App\Interfaces
 */
interface ClientInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    public function getInstance($key = '');//获取客户端实例

    public function __construct(Container $app);
}