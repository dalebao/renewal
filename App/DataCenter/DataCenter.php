<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 上午10:50
 */

namespace App\DataCenter;


use App\Interfaces\ModelInterface;

/**
 * Class DataCenter
 * @package App\DataCenter
 */
class DataCenter
{
    /**
     * @var
     */
    private $data_type;//数据类型 refund or renewal
    /**
     * @var
     */
    private $data_model;
    /**
     * @var string
     */
    private $model_namespace = '\\App\\Model\\';

    /**
     * @param $data_type
     */
    public function setDataType($data_type)
    {
        $this->data_type = ucfirst($data_type);
        $this->setDataModel();
    }


    /**
     *
     */
    private function setDataModel()
    {
        $modle = "\\App\\Model\\{$this->data_type}";
        $this->data_model = new $modle;
    }

    /**
     * @return mixed
     */
    public function action()
    {
        app('log','runtime')->info('操作类型',['type'=>$this->data_type]);
        app('log','runtime')->info('开始时间',['start_time'=>date('Y-m-d H:i:s',time())]);
        $data = $this->data_model->getData();
        app('log','runtime')->info('结束时间',['end_time'=>date('Y-m-d H:i:s',time())]);

        return $data;
    }

    /**
     * @param $info
     * @return mixed
     */
    public function handleSingle($info)
    {
        return $this->data_model->getSingleData($info);
    }



}