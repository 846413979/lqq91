<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\enum\Order as OrderEnum;
use app\common\library\Cart as CartLibrary;

/**
 * 购物车接口
 */
class Order extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';
    public $cartLibrary;

    public function _initialize()
    {
        parent::_initialize();
        $this->cartLibrary = new CartLibrary();
    }


    public function addOrder(){
        if(!$this->request->isPost()){
            $this->error("请求错误");
        }
        $data = $this->request->param();
        $result = $this->validate($data,"Order");
        if($result!==true){
            $this->error($result);
        }
        $payType = $data['pay_type'];
        if($payType<=OrderEnum::UnknowPayType || $payType>=OrderEnum::endPayType){
            $this->error("请选择正确的支付方式");
        }
        $user_id = 2;
        $checkCart = $this->cartLibrary->checkCart($data["cart_ids"],$user_id);
        if(!$checkCart){
            $this->error("不是本人购物车，不可操作");
        }

    }


}
