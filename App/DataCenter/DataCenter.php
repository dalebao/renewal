<?php
/**
 * Created by PhpStorm.
 * User: baoxulong
 * Date: 2018/6/4
 * Time: 上午10:50
 */

namespace App\DataCenter;


use App\Interfaces\ModelInterface;

class DataCenter
{
    private $data_type;//数据类型 refund or renewal
    private $data_model;
    private $model_namespace = '\\App\\Model\\';

    public function setDataType($data_type)
    {
        $this->data_type = ucfirst($data_type);
        $this->setDataModel();
    }


    private function setDataModel()
    {
        $modle = "\\App\\Model\\{$this->data_type}";
        $this->data_model = new $modle;
    }

    public function test()
    {
        return $this->data_model->getData();
    }

    public function handleSingle($info)
    {
        return $this->data_model->getSingleData($info);
    }



}