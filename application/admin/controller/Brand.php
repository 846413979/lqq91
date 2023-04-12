<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;

/**
 * 品牌管理
 *
 * @icon fa fa-circle-o
 */
class Brand extends Backend
{

    /**
     * Brand模型对象
     * @var \app\admin\model\Brand
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Brand;

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

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
        $data = Db::name('cart')->where(['brand_id'=>$id])->select();
        if(!empty($data)){
            foreach ($data as $v){
                $brand_discount = Db::name('brand_discount')->where(['user_id'=>$v['user_id'],'brand_id'=>$id])->find();
                if($brand_discount){
                    $discount=$brand_discount['discount'];
                    $goods_price =  sprintf("%.2f",($discount/100)*$v['goods_original_price']*$v['goods_num']);
                }else{
                    $discount=0;
                    $goods_price =  sprintf("%.2f",$v['goods_original_price']*$v['goods_num']);
                }

                Db::name('cart')->where('id',$v['id'])
                    ->update([
                        'brand_name'=>$params['name'],
                        'goods_price'=>$goods_price,
                        'user_brand_discount'=>$discount,
                    ]);
            }
        }
        return true;
    }

}
