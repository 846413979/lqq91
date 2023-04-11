<?php

namespace app\common\model;

use think\Model;


class Goods extends Model
{

    // 表名
    protected $name = 'goods';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];

    // 关联分类
    public function category(){
        return $this->belongsTo("category","category_id");
    }

    //关联品牌
    public function brand(){
        return $this->belongsTo("brand");
    }


}
