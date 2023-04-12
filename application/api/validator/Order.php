<?php

namespace app\api\validate;

use think\Validate;

class Order extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        "cart_ids"    => "require",
        "address_id"  => "require|number",
        "has_invoice" => "require|bool",
        "invoice_id"  => "requireWith:has_invoice|number",
        "pay_type"    => "require|number",
    ];
    /**
     * 提示消息
     */
    protected $message = [
        "cart_ids"    => "参数错误",
        "address_id"  => "请选择地址",
        "has_invoice" => "请选择是否开票",
        "invoice_id"  => "请选择发票信息",
        "pay_type"    => "请选择支付方式",

    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [],
        'edit' => [],
    ];

}
