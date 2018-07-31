<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 上午10:49
 */

namespace App\Model;


use App\Interfaces\ModelInterface;
use App\Utils\ServiceContainer;
use Carbon\Carbon;

class Refund implements ModelInterface
{
    private $today;
    private $first_day;
    private $last_day;
    private $start_time;
    private $end_time;
    private $expire_time;
    private $db;
    private $log;
    private $db_log;
    private $redis;
    private $producer;

    /**
     * Refund constructor.
     * @throws \AMQPConnectionException
     */
    public function __construct()
    {
        $this->today = $this->today = Carbon::now('Asia/Shanghai')->format('Y-m-d');
        $this->first_day = Carbon::now('Asia/Shanghai')->firstOfMonth()->format('Y-m-d');
        $this->last_day = Carbon::now('Asia/Shanghai')->lastOfMonth()->format('Y-m-d');
        $this->start_time = Carbon::yesterday('Asia/Shanghai')->format('Y-m-d 18:00:00');
        $this->end_time = Carbon::today('Asia/Shanghai')->format('Y-m-d 00:00:00');
        $this->expire_time = Carbon::now('Asia/Shanghai')->lastOfMonth()->format('Y-m-d 23:59:59');


        $this->log = app('log');
        $this->db_log = app('log', 'db');
        $this->db = app('db');
        $this->redis = app('redis');
        $container = new ServiceContainer();
        $this->producer = $container->producer->getInstance();

    }

    public function getData()
    {
        // TODO: Implement getData() method.
        if ($this->today == $this->first_day) {
            $data = $this->getAllData();
        }

        if (!empty($data)) {
            foreach ($data as $datum) {
                $this->handleData($datum);
            }
        }

    }

    /**
     * @param $data
     * @return mixed
     */
    public function getSingleData($info)
    {
        $data = $this->getSingleAccountProduct($info);

        if (!empty($data)) {
            $this->handleData($data);
        }

        return $data;
    }


    protected function getAllData()
    {
        return $this->db->table('switch_renew_log')
            ->leftJoin('account', function ($join) {
                $join->on('switch_renew_log.id6d', '=', 'account.id6d');
            })
            ->leftJoin('order', function ($join) {
                $join->on('account.account_key', '=', 'order.account_key');
            })
            ->leftJoin('company', function ($join) {
                $join->on('company.company_id', '=', 'switch_renew_log.company_id');
            })
            ->where('switch_renew_log.auto_renew', '=', 0)
            ->where('switch_renew_log.remarks', '=', '手动修改续费开关')
            ->whereBetween('switch_renew_log.alter_time', [$this->start_time, $this->end_time])
            ->whereBetween('order.order_time', [$this->start_time, $this->end_time])
            ->where('order.now_expire_time', '=', $this->expire_time)
            ->select('account.id6d',
                'company.facilitator_id',
                'order.order_key',
                'order.product_key',
                'order.meal_key',
                'order.account_key',
                'order.company_id',
                'order.pay_account',
                'order.paycompany_id',
                'order.order_amount')
            ->get();
    }

    protected function getSingleAccountProduct($data)
    {
        $meal_key = $data['meal_key'];
        $id6d = $data['id6d'];


        $query = $this->db->table('switch_renew_log')
            ->leftJoin('account', function ($join) {
                $join->on('switch_renew_log.id6d', '=', 'account.id6d');
            })
            ->leftJoin('order', function ($join) {
                $join->on('account.account_key', '=', 'order.account_key');
            })
            ->leftJoin('company', function ($join) {
                $join->on('company.company_id', '=', 'switch_renew_log.company_id');
            });
        if (isset($id6d) && !empty($id6d)) {
            $query->where('account.id6d', $id6d);
        }
        if (isset($meal_key) && !empty($meal_key)) {
            $query->where('account.id6d', $meal_key);
        }

        return $query->where('switch_renew_log.auto_renew', '=', 0)
            ->where('switch_renew_log.remarks', '=', '手动修改续费开关')
            ->whereBetween('switch_renew_log.alter_time', [$this->start_time, $this->end_time])
            ->whereBetween('order.order_time', [$this->start_time, $this->end_time])
            ->where('order.now_expire_time', '=', $this->expire_time)
            ->select('account.id6d',
                'company.facilitator_id',
                'order.order_key',
                'order.product_key',
                'order.meal_key',
                'order.account_key',
                'order.company_id',
                'order.pay_account',
                'order.paycompany_id',
                'order.order_amount')
            ->first();
    }

    protected function handleData($data)
    {

        $order = [
            'init_saas_order_id' => $data->order_key,    //原订单号
            'account_id' => $data->account_key,        //账号
            'company_id' => $data->company_id,        //公司ID
            'id6d' => $data->id6d,            //工号
            'pay_account' => $data->pay_account,        //支付账号
            'paycompany_id' => $data->paycompany_id, //支付公司ID
            'product_id' => $data->product_key,        //产品键值
            'meal_key' => $data->meal_key,            //套餐键值
            'order_type' => '2',                    //订单类型(1:销售,2:退货)
            'order_amount' => $data->order_amount,    //扣费金额
            'product_unit' => '1',                    //单位
            'facilitator_id' => '1',                //服务商ID
            'cmd' => 'renew_order',
            'refund_type' => 'all',
            'order_remarks' => '月末修改自动续费开关的退款', //订单备注
            '53kf_token' => 'Aj|uU620cjJ`53kf'];
        app('log')->info('数据类型', ['type' => 'refund']);
        app('log')->info('sql-筛选出的数据', ['data' => $data]);
        app('log')->info('拼装之后的数据', ['order' => $order]);

        $order['account_id'] = decrypt_6d($order['account_id']);
        $order['pay_account'] = decrypt_6d($order['pay_account']);

        $mq_data['type'] = 'refund';
        $mq_data['orders'] = $order;
        $redis_key = 'saas.facilitator.refund.' . $data->company_id . '.' . $data->id6d;
        $val = $this->redis->hget($redis_key, $data->meal_key);

        if (empty($val)) {
            $this->redis->hmset($redis_key, [$data->meal_key => '1']);
            $this->redis->expire($redis_key, 120);//120 秒过期
            $this->producer->exec(json_encode($mq_data));
        }
    }


}