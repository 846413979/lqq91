<?php

namespace app\common\library;

use app\common\model\Cart as CartModel;
use app\common\model\Goods;
use think\Controller;

class Order extends Controller
{

    /**
     * @var string
     */
    private $_error;
    protected $goods;
    protected $cart;

    protected static $instance;


    public function __construct()
    {
        $this->cart = new CartModel();
        $this->goods = new Goods();
    }


    /**
     * 设置错误信息
     *
     * @param string $error 错误信息
     * @return Cart
     */
    public function setError($error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->_error ? __($this->_error) : '';
    }
}
