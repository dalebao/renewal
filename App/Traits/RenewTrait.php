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
            $this->log->info('company_arr', ['com' => $this->company_arr]);
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


        $redis_key = 'saas.facilitator.' . $data->company_id . '.' . $data->id6d;

        $val = $this->redis->hget($redis_key, $data->meal_key);
        $rows['account_id'] = decrypt_6d($rows['account_id']);
        $rows['pay_account'] = decrypt_6d($rows['pay_account']);

        $mq_data = [
            'type' => 'renewal',
            'orders' => $rows
        ];
        if (strtotime($this->now) >= $time && empty($val)) {
            $this->redis->hmset($redis_key, [$data->meal_key => '1']);
            $this->redis->expire($redis_key, 120);//120 秒过期
//                $this->log->info('data', ['info' => $data]);
            $this->producer->exec(json_encode($mq_data));
        }

    }


}