<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/11
 * Time: 上午11:31
 */

namespace App\DataCenter;


class DataHandler
{
    private $api;
    private $type;
    private $redis;
    private $war_log;
    private $info_log;
    private $pid;

    public function __construct()
    {
        $this->redis = app('redis');
        $this->war_log = app('log', 'war');
        $this->info_log = app('log', 'info');
    }

    public function handleData($info, $pid)
    {
        $this->pid = $pid;
        switch ($info['type']) {
            case 'renewal':
                $this->api = 'http://api.saas.71baomu.com/order?cmd=renew_order';
                $this->type = 'renewal';
                break;
            case 'refund':
                $this->api = 'http://api.saas.71baomu.com/order';
                $this->type = 'refund';
        }

        //为2表示已经请求续费 设置redis键过期两小时
        if ($this->checkRedis($info['orders']) == 2) {
            return;
        }

        $orders = $info['orders'];
        $res = curl_post($this->api, $orders);
        $this->info_log->info('number', ['1' => 1]);
        $this->war_log->warn('result of request', ['res' => $res]);
        $this->war_log->info('time of request', ['time' => date('Y-m-d H:i:s')]);
        $this->war_log->info('details of orders', ['orders' => $orders]);
        $this->war_log->warn('pid of this process', ['pis' => $this->pid]);
        $this->setRedis($orders);
    }

    protected function checkRedis($orders)
    {
        return $this->redis->hget('saas.facilitator.' . $this->type . '.' . $orders['company_id'] . '.' . $orders['id6d'], $orders['meal_key']);
    }

    protected function setRedis($orders)
    {
        $this->redis->hmset('saas.facilitator.' . $this->type . '.' . $orders['company_id'] . '.' . $orders['id6d'], [$orders['meal_key'] => 2]);
        $this->redis->expire('saas.facilitator.' . $this->type . '.' . $orders['company_id'] . '.' . $orders['id6d'], 2 * 3600);
    }


}