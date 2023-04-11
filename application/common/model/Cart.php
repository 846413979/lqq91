<?php

namespace app\common\model;

use think\Db;
use think\Model;

/**
 * 购物车模型
 */
class Cart extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [

    ];

    public function saveData($data,$isUpdate = false){
        return $this->allowField(true)->isUpdate($isUpdate)->save($data);
    }


}
