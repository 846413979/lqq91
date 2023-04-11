<?php

namespace app\common\library;

use app\common\model\Cart as CartModel;
use app\common\model\Goods;
use think\Controller;

class Cart extends Controller
{

    /**
     * @var string
     */
    private $_error;
    protected $goods;
    protected $cart;

    protected static $instance;
    const OpAdd = 1;
    const OpMinus = 2;


    public function __construct()
    {
        $this->cart = new CartModel();
        $this->goods = new Goods();
    }

    /**
     * 获取购物车列表
     * @param int $user_id 用户id
     * @return array|bool|\PDOStatement|string|\think\Collection
     */
    public function cartList($user_id)
    {
        $goods_list = $this->cart->all(["user_id" => $user_id]);
        $list = $this->cart->where('user_id', $user_id)->group("brand_id")->field("brand_id,brand_name")->select();

        foreach ($list as &$value) {
            foreach ($goods_list as $v) {
                if ($v->brand_id == $value->brand_id) {
                    $value->goods_list = $v;
                }
            }
        }
        return $list;
    }


    /**
     * 添加购物车
     * @param int $goods_id 商品id
     * @param int $num 数量
     * @param int $user_id 用户id
     */
    public function addCart($goods_id, $num, $user_id)
    {
        // 查询商品是否存在
        $goods = $this->goods->with("category,brand")->find($goods_id);
        if (empty($goods)) {
            $this->setError("商品不存在");
            return false;
        }
        // 查询用户品牌折扣
        $discount = db("brand_discount")->where(["user_id" => $user_id, "brand_id" => $goods->brand_id])->value("discount");
        if (empty($discount)) {
            // 查询不到，无折扣，100%
            $discount = 100;
        }
        // 查询购物车是否存在此商品
        $cart_goods = $this->cart->get(["goods_id" => $goods_id, "user_id" => $user_id]);
        $data = array(
            "brand_id"             => $goods->brand ? $goods->brand->id : 0,
            "brand_name"           => $goods->brand ? $goods->brand->name : "",
            "goods_name"           => $goods->name,
            "goods_no"             => $goods->sku,
            "goods_original_price" => $goods->price,
            "user_brand_discount"  => $discount,
            "goods_price"          => $goods->price * $discount / 100,
            "goods_pack"           => $goods->pack,
            "goods_stock"          => $goods->stock,
        );

        if (!empty($cart_goods)) {
            // 购物车已存在
            // 添加数量
            $data["goods_num"] = $cart_goods->goods_num + $num;
            $data["id"] = $cart_goods->id;
            $res = $this->cart->saveData($data, true);
        } else {
            //购物车不存在
            // 添加商品
            $data["user_id"] = $user_id;
            $data["goods_id"] = $goods_id;
            $data["goods_num"] = $num;
            $res = $this->cart->saveData($data);
        }
        if (empty($res)) {
            $this->setError("write mysql error");
            return false;
        }
        return true;

    }


    /**
     * 更新商品数量
     * @param int $id 购物车id
     * @param int $user_id 用户id
     * @param int $op 操作：(OpAdd:+;OpMinus:-)
     * @param int $num 商品数量，如果有值，忽略操作，直接更新购物车商品数量
     */
    public function updateGoodsNum($id, $user_id, $op, $num = 0)
    {
        // 查询当前购物车商品数量
        $cart_data = $this->cart->where("id", $id)->field("user_id,goods_num")->find();
        if (empty($cart_data)) {
            $this->setError("商品不存在");
            return false;
        }
        if ($cart_data["user_id"] != $user_id) {
            $this->error("参数错误");
        }
        $current_num = $cart_data['goods_num'];
        if ($num != 0) {
            // 传入数量，忽略操作，直接更新购物车商品数量
            $res = $this->cart->where('id', $id)->setField("goods_num", $num);
            if (empty($res)) {
                $this->setError("write mysql error");
                return false;
            }
            return true;
        }

        if ($current_num == 1 && $op == self::OpMinus) {
            // 当前购物车商品数量是0，并且操作为减，删除
            $res = $this->cart->destroy($id);
            if (empty($res)) {
                $this->setError("write mysql error");
                return false;
            }
            return true;
        }

        if ($op == self::OpAdd) {
            $data = array("goods_num" => $current_num + 1);
        } else {
            $data = array("goods_num" => $current_num - 1);

        }
        $res = $this->cart->where('id', $id)->update($data);
        if (empty($res)) {
            $this->setError("write mysql error");
            return false;
        }
        return true;
    }

    /**
     * 删除购物车商品
     * @param string $goods_ids 商品id，多个用逗号分割
     */
    public function deleteCart($goods_ids, $user_id)
    {
        $ids_arr = explode(",", $goods_ids);
        $res = $this->cart->destroy(["goods_id" => ["in", $ids_arr], "user_id" => $user_id]);
        if (empty($res)) {
            $this->setError("write mysql error");
            return false;
        }
        return true;
    }

    /**
     * 清空购物车
     * @param int $user_id 用户id
     */
    public function emptyCart($user_id)
    {
        $res = $this->cart->destroy(["user_id" => $user_id]);
        if (empty($res)) {
            $this->setError("write mysql error");
            return false;
        }
        return true;
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
