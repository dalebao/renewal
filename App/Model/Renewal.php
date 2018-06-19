<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 上午10:49
 */

namespace App\Model;


use App\Interfaces\ModelInterface;
use App\Traits\RenewTrait;
use App\Utils\ServiceContainer;
use Carbon\Carbon;

/**
 * Class Renewal
 * @package App\Model
 */
class Renewal implements ModelInterface
{
    use RenewTrait;
    private $now;
    private $now_d;
    private $now_t;
    private $now_timestamp;
    private $stop_time;
    private $db;
    private $log;
    private $db_log;
    private $redis;
    private $producer;

    private $company_arr = [];

    /**
     * Renewal constructor.
     * @throws \AMQPConnectionException
     */
    public function __construct()
    {
        $this->now = Carbon::now('Asia/Shanghai')->format('Y-m-d 23:59:59');
        $this->now_d = Carbon::now('Asia/Shanghai')->format('d');
        $this->now_t = Carbon::now('Asia/Shanghai')->format('t');
        $this->now_timestamp = Carbon::now('Asia/Shanghai')->timestamp;
        $this->stop_time = strtotime(Carbon::now('Asia/Shanghai')->format('Y-m-d 17:50:00'));
        $this->log = app('log');
        $this->db_log = app('log', 'db');
        $this->db = app('db');
        $this->redis = app('redis');
        $container = new ServiceContainer();
        $this->producer = $container->producer->getInstance();
    }

    /**
     * @return mixed|void
     */
    public function getData()
    {
        foreach ($this->getAccountProduct() as $data) {
            if (!empty($data)) {
                $this->handleData($data);
            }
        }
    }

    /**
     * 单公司单条续费
     * @param $info
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


    public function getSingleAccountProduct($info)
    {
        $company_id = $info['company_id'];
        $meal_key = $info['meal_key'];

        $query = $this->db->table('account_product')
            ->rightJoin('account', function ($join) {
                $join->on('account_product.account_key', '=', 'account.account_key');
            })
            ->leftJoin('company', function ($join) {
                $join->on('account_product.company_id', '=', 'company.company_id');
            })
            ->leftJoin('product_meal', function ($join) {
                $join->on('product_meal.meal_key', '=', 'account_product.meal_key');
            })
            ->where('account_product.auto_renew', 1)
            ->where('company.stopping', '=', 1)
            ->where('account_product.meal_key', '=', $meal_key)
            ->where('company.company_id', '=', $company_id);
        if (isset($info['id6d']) && !empty($info['id6d'])) {
            $query->where('account.id6d', '=', $info['id6d']);
        }

        $data = $query->where('product_meal.time_unit', '=', 'month')
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
        return $data;
    }


    public function getAccountProduct()
    {
        $fill_status = true;
        $take = 1000;
        $skip = 0;
        while ($fill_status) {
            $data = $this->db->table('account_product')
                ->rightJoin('account', function ($join) {
                    $join->on('account_product.account_key', '=', 'account.account_key');
                })
                ->leftJoin('company', function ($join) {
                    $join->on('account_product.company_id', '=', 'company.company_id');
                })
                ->leftJoin('product_meal', function ($join) {
                    $join->on('product_meal.meal_key', '=', 'account_product.meal_key');
                })
                ->where('account_product.auto_renew', 1)
                ->where('company.stopping', '=', 1)
                ->where('product_meal.time_unit', '=', 'month')
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
            $this->db_log->info('query', ['res' => $data]);
            $company_id = $data->company_id;
            $max_expire_time = $this->db
                ->table('account_product')
                ->selectRaw('max(expire_time) as t')
                ->where('company_id', '=', $company_id)
                ->where('expire_time', '<', '2040-01-01')
                ->first();
            $max_expire_time = strtotime($max_expire_time->t);

//            $this->redis->expire('saas.facilitator.' . $data->company_id . '.' . $data->id6d,1);
            if ($max_expire_time == false || $max_expire_time < strtotime($this->now) - 7 * 24 * 3600) {
                //使公司停机
                $this->stopCompany($company_id);
            } else {
                //处理订单数据
                $this->handleOrder($data);
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
                $order_amount = (((int)$this->now_t - $this->now_d) + 1) / $this->now_t;
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

            $this->sendMsgAndSetLock($rows, $data, $time);


        }
    }


}