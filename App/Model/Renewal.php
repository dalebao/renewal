<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 上午10:49
 */

namespace App\Model;


use App\Interfaces\ModelInterface;
use App\Lib\Producer;
use Carbon\Carbon;

/**
 * Class Renewal
 * @package App\Model
 */
class Renewal implements ModelInterface
{
    private $now;
    private $now_d;
    private $now_t;
    private $now_timestamp;
    private $stop_time;
    private $db;
    private $log;
    private $redis;
    private $producer;

    private $company_arr = [];

    /**
     * @return mixed|void
     */
    public function getData()
    {
        $this->now = Carbon::now('Asia/Shanghai')->format('Y-m-d 23:59:59');
        $this->now_d = Carbon::now('Asia/Shanghai')->format('d');
        $this->now_t = Carbon::now('Asia/Shanghai')->format('t');
        $this->now_timestamp = Carbon::now('Asia/Shanghai')->timestamp;
        $this->stop_time = strtotime(Carbon::now('Asia/Shanghai')->format('Y-m-d 17:50:00'));
        $this->log = app('log');
        $this->db = app('db');
        $this->redis = app('redis');
        $config = [
            'host' => '172.19.0.4',
            'port' => '5672',
            'login' => 'guest',
            'password' => 'guest',
            'vhost' => '/'
        ];
        $e_name = 'e_linvo'; //交换机名
        $q_name = 'q_linvo'; //队列名
        $k_route = 'key_1'; //路由key
        $this->producer = Producer::init($config)->setExchange($e_name)->bind($k_route);

        foreach ($this->getAccountProduct() as $data) {
            $this->handleData($data);
        }


    }

    public function getAccountProduct()
    {
        $fill_status = true;
        $take = 1000;
        $skip = 0;
        while ($fill_status) {
            $data = $this->db->table('account_product')
                ->leftJoin('account', function ($join) {
                    $join->on('account_product.account_key', '=', 'account.account_key');
                })
                ->leftJoin('company', function ($join) {
                    $join->on('account_product.company_id', '=', 'company.company_id');
                })
                ->leftJoin('product_meal', function ($join) {
                    $join->on('product_meal.meal_key', '=', 'account_product.meal_key');
                })
                ->where('account_product.auto_renew', '1')
                ->where('company.stopping', '=', 1)
                ->take($take)
                ->skip($skip)
                ->select('account_product.expire_time',
                    'account_product.product_key',
                    'product_meal.time_unit',
                    'account_product.meal_key',
                    'account_product.account_key',
                    'account_product.company_id',
                    'account_product.product_unit',
                    'account.id6d',
                    'company.facilitator_id',
                    'account.paycompany_id',
                    'account.pay_id6d',
                    'account.pay_account')
                ->get();
            $skip += $take;
            if (count($data) < $take) {
                $fill_status = false;
            }
            yield $data;
        }

    }

    public function handleData($datas)
    {
        foreach ($datas as $data) {
            $company_id = $data->company_id;
            $max_expire_time = $this->db
                ->table('account_product')
                ->selectRaw('max(expire_time) as t')
                ->where('company_id', '=', $company_id)
                ->where('expire_time', '<', '2040-01-01')
                ->first();
            $max_expire_time = strtotime($max_expire_time->t);

            if ($max_expire_time == false || $max_expire_time < strtotime($this->now) - 7 * 24 * 3600) {
                //使公司停机
                $this->stopCompany($company_id);
            } else {
                //处理订单数据
                $this->handleOrder($data);
            }
        }

    }

    private function stopCompany($company_id)
    {
        if (!in_array($company_id, $this->company_arr)) {
            $this->company_arr[] = $company_id;
            $stopping = $this->db->table('company')->select('stopping')->where('company_id', $company_id)->first();
            if ($stopping->stopping == 1) {
//            var_dump($company_id);
                //TODO:: 让公司停机
            }
        }
    }

    public function handleOrder($data)
    {
        $expire_time = strtotime($data->expire_time);
        //产品单位是月 就走自动续费
        if ($data->time_unit == 'month') {
            //如果当前日期等于月底 且 到期时间等于当天23：59：59 且 当前时间大于17点
            if ($this->now_d == $this->now_t && strtotime($this->now) == $expire_time && $this->now_timestamp > $this->stop_time) {
                //续费一个月
                $order_amount = 1;
            } else if (strtotime($this->now) > $expire_time) {
                //否则数量等于当月总天数/当前日期到月底的剩余天数
                $order_amount = (($this->now_t - $this->now_d) + 1) / $this->now_t;
            } else {
                //继续下一个账号
                return;
            }
            if (empty($data->pay_id6d)) {
                $data->pay_id6d = $data->id6d;
            }
            if (empty($data->pay_account)) {
                $data->pay_account = $data->account_key;
            }
            if (empty($data->paycompany_id)) {
                $data->paycompany_id = $data->company_id;
            }

            $rows = [
                'facilitator_id' => $data->facilitator_id,    //服务商ID
                'order_type' => 1,                            //订单类型(1:销售,2:退货)
                'product_id' => $data->product_key,        //产品id
                'id6d' => $data->id6d,                    //工号
                'pay_id6d' => $data->pay_id6d,                //支付id6d
                'meal_key' => $data->meal_key,                //套餐ID
                'account_id' => $data->account_key,        //账号
                'company_id' => $data->company_id,        //公司ID
                'pay_account' => $data->pay_account,        //代支付账号
                'order_remarks' => '自动续费',                //订单备注
                'paycompany_id' => $data->paycompany_id,    //代支付公司ID
                'product_unit' => $data->product_unit,        //单位
                'order_amount' => $order_amount,            //产品数量
                'cmd' => 'renew_order',
                '53kf_token' => 'Aj|uU620cjJ`53kf'];

            $a_expire_time = $this->db->table('account_product')->where('account_key', $data->account_key)
                ->where('company_id', $data->company_id)
                ->where('meal_key', $data->meal_key)
                ->where('product_key', $data->product_key)
                ->select('expire_time')
                ->first();

            $time = strtotime($a_expire_time->expire_time);
            $redis_key = 'saas.facilitator.' . $data->company_id . '.' . $data->id6d;
            if ($data->company_id == '72001902' || $data->company_id == 72000351) {
                app('log','war')->warn('data', ['info' => $data->id6d]);
            }
            $val = $this->redis->hget($redis_key, $data->meal_key);
            if (strtotime($this->now) >= $time && empty($val)) {
                $this->redis->hmset($redis_key, [$data->meal_key => '1']);
                $this->redis->expire($redis_key, 120);//120 秒过期
                $this->log->info('data', ['info' => $data]);
                $this->producer->exec(json_encode($rows));
            }

        }
    }


}