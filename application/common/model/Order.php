<?php

namespace app\common\model;

use think\Model;


class Order extends Model
{

    // 表名
    protected $name = 'order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    protected $type = [
        "order_member_info"=>"array",
        "consignee_info"=> "array",
        "invoice_info"=>"array",
        ""
    ];



}
