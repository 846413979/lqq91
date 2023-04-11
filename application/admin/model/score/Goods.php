<?php

namespace app\admin\model\score;

use think\Model;


class Goods extends Model
{
    // 表名
    protected $name = 'score_goods';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;


}
