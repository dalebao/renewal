<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/14
 * Time: 下午1:47
 */

namespace App\Traits;


trait RenewTrait
{
    private $company_arr = [];

    private function stopCompany($company_id)
    {
        if (!in_array($company_id, $this->company_arr)) {
            app('log')->info('停机公司id', ['com' => $company_id]);
            $this->company_arr[] = $company_id;
//            $stopping = $this->db->table('company')->select('stopping')->where('company_id', $company_id)->first();
//            if ($stopping->stopping == 1) {
            //TODO:: 让公司停机
            $this->db->table('company')->where('company_id', $company_id)->update(['stopping' => '2']);
//            }
        }
    }


    private function sendMsgAndSetLock($rows, $data, $time)
    {
        app('log')->info('未处理的数据', ['data' => $data]);
        app('log')->info('处理之后的数据', ['rows' => $rows]);
        $redis_key = 'saas.facilitator.renewal.' . $data->company_id . '.' . $data->id6d;

        $val = $this->redis->hget($redis_key, $data->meal_key);
        $rows['account_id'] = decrypt_6d($rows['account_id']);
        $rows['pay_account'] = decrypt_6d($rows['pay_account']);

        $mq_data = [
            'type' => 'renewal',
            'orders' => $rows
        ];
        app('log')->info('写入rabbitMq的数据', ['mq_data' => $mq_data]);
        if (strtotime($this->now) >= $time && empty($val)) {
        $this->redis->hmset($redis_key, [$data->meal_key => '1']);
        $this->redis->expire($redis_key, 120);//120 秒过期
        app('log')->info('data', ['info' => $data]);
        $this->producer->exec(json_encode($mq_data));
    }

    }


}