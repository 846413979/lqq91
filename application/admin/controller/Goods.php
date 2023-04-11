<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\library\Auth;
use fast\Tree;
use think\Db;

/**
 * 商品管理
 *
 * @icon fa fa-circle-o
 */
class Goods extends Backend
{

    /**
     * Goods模型对象
     * @var \app\admin\model\Goods
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Goods;

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
            foreach ($list as $v){
                $v->category_id = Db::name('category')->where('id',$v->category_id)->value('name');
                $v->brand_id = Db::name('brand')->where('id',$v->brand_id)->value('name');
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }

        return $this->view->fetch();
    }


    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a");

            if ($params) {
                $params = $this->preExcludeFields($params);

                $result = $row->allowField(true)->save($params);
                $this->cart($params,$ids);
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error($row->getError());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    //购物车价格改变
    public function cart($params,$id)
    {
        $data = Db::name('cart')->where(['goods_id'=>$id])->select();
        if(!empty($data)){
            foreach ($data as $v){
                $brand_name = Db::name('brand')->where('id',$params['brand_id'])->value('name');
                $brand_discount = Db::name('brand_discount')->where(['user_id'=>$v['user_id'],'brand_id'=>$params['brand_id']])->find();
                if($brand_discount){
                    $discount=$brand_discount['discount'];
                    $goods_price =  sprintf("%.2f",($discount/100)*$params['price']*$v['goods_num']);
                }else{
                    $discount=0;
                    $goods_price =  sprintf("%.2f",$params['price']*$v['goods_num']);
                }

                Db::name('cart')->where('id',$v['id'])
                    ->update([
                        'brand_id'=>$params['brand_id'],
                        'brand_name'=>$brand_name,
                        'goods_original_price'=>$params['price'],
                        'goods_price'=>$goods_price,
                        'goods_name'=>$params['name'],
                        'goods_no'=>$params['sku'],
                        'user_brand_discount'=>$discount,
                        'goods_pack'=>$params['pack'],
                        'goods_stock'=>$params['stock'],
                    ]);
            }
        }
        return true;
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        $this->model->destroy($ids);
        Db::name('cart')->where('goods_id','in',$ids)->update(['goods_status'=>0]);
        $this->success();
    }

}
