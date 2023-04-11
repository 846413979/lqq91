<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Cart as CartLibrary;
use app\common\enum\Cart as CartEnum;

/**
 * 购物车接口
 */
class Cart extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';
    public $cartLibrary;

    public function _initialize()
    {
        parent::_initialize();
        $this->cartLibrary = new CartLibrary();
    }

    /**
     * 购物车列表
     */
    public function index()
    {
        $user_id = 2;
        $res = $this->cartLibrary->cartList($user_id);
        if ($res) {
            $this->success("查询成功", $res);
        } else {
            $this->error("查询失败");
        }
    }


    /**
     * 加入购物车
     *
     * @ApiMethod (POST)
     * @param int $goods_id 商品id
     * @param int $num 商品数量
     */
    public function addCart()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $param = request()->param();
        if (empty($param['goods_id'])) {
            $this->error("商品id不能为空");
        }
        if (empty($param['num'])) {
            $this->error("商品数量不能为空");
        }
        $user_id = 2;
        $res = $this->cartLibrary->addCart($param['goods_id'], $param['num'], $user_id);
        if ($res) {
            $this->success("添加成功");
        } else {
            $this->error($this->cartLibrary->getError());
        }
    }

    /**
     * 更新购物车商品数量
     * @param int $id 购物车id
     * @param int $op 操作类型：1加，2减
     * @param int $num 商品数量：如此字段不为空，表无视操作类型，更新商品数量；
     **/
    public function updateGoodsNum()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $param = request()->param();
        if (empty($param['id'])) {
            $this->error("参数错误");
        }
        $op = intval($param["op"]);
        if (empty($op) || ($op != CartEnum::OpAdd && $op != CartEnum::OpMinus)) {
            $this->error("操作类型错误");
        }
        $user_id = 2;
        $res = $this->cartLibrary->updateGoodsNum($param["id"], $user_id, $op, !empty($param["num"]) ? $param["num"] : 0);
        if ($res) {
            $this->success("更新成功");
        } else {
            $this->error($this->cartLibrary->getError());
        }
    }


    /**
     * 删除购物车商品
     * @param string $ids 商品id，多个用逗号分割
     **/
    public function deleteCart()
    {
        $ids = $this->request->param("ids");
        if (empty($ids)) {
            $this->error("参数错误");
        }
        $user_id = 2;
        $res = $this->cartLibrary->deleteCart($ids, $user_id);
        if ($res) {
            $this->success("删除成功");
        } else {
            $this->error($this->cartLibrary->getError());
        }
    }


    /**
     * 清空购物车商品
     **/
    public function emptyCart()
    {
        $user_id = 2;
        $res = $this->cartLibrary->emptyCart($user_id);
        if ($res) {
            $this->success("删除成功");
        } else {
            $this->error($this->cartLibrary->getError());
        }
    }
}
