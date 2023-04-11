<?php

namespace app\common\model;

use think\Model;


class BrandDiscount extends Model
{

    // 表名
    protected $name = 'brand_discount';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


}
