<?php

namespace app\admin\controller\recharge;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 充值活动管理
 *
 * @icon fa fa-circle-o
 */
class Setting extends Backend
{

    /**
     * Setting模型对象
     * @var \app\admin\model\recharge\Setting
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\recharge\Setting;

    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function add()
    {
        if (false === $this->request->isPost()) {
            $coupon = Db::name('coupon')->select();
            $this->assign('coupon',$coupon);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');

        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);

        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }

        $result = $this->model->allowField(true)->save($params);

        if(!empty($params['event'])){
            $event = json_decode($params['event'],true);

            foreach ($event as $v){
                Db::name('recharge_setting_coupon')->insert(['recharge_setting_id'=>$this->model->id,'num'=>$v['number'],'coupon_id'=>$v['id']]);
            }
        }

        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }

    //设置品牌折扣
    public function edit($ids=NULL)
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
            if(!empty($params['event'])){
                $event = json_decode($params['event'],true);

                foreach ($event as $v){
                    $find = Db::name('recharge_setting_coupon')->where(['recharge_setting_id'=>$params['id'],'coupon_id'=>$v['id']])->find();
                    if($find){
                        Db::name('recharge_setting_coupon')->where(['recharge_setting_id'=>$params['id'],'coupon_id'=>$v['id']])->update(['num'=>$v['num']]);
                    }else{
                        Db::name('recharge_setting_coupon')->insert(['recharge_setting_id'=>$params['id'],'num'=>$v['num'],'coupon_id'=>$v['id']]);
                    }
                }
                $oldCategoryIds = Db::name('recharge_setting_coupon')->where(['recharge_setting_id'=>$params['id']])->column('coupon_id');
                $categories        = array_column($event,'id');

                $sameCategoryIds       = array_intersect($categories, $oldCategoryIds);
                $needDeleteCategoryIds = array_diff($oldCategoryIds, $sameCategoryIds);
                Db::name('recharge_setting_coupon')->where(['recharge_setting_id'=>$params['id'],'coupon_id'=>['in',$needDeleteCategoryIds]])->delete();
                $this->success();
            }

            $this->error(__('Parameter %s can not be empty', ''));
        }

        $list = Db::name('recharge_setting_coupon')->where('recharge_setting_id',$ids)
            ->field('coupon_id as id,num')->select();

        $this->assign('row',['list'=>json_encode($list,true)]);
        $this->view->assign("data", $row);
        return $this->view->fetch();
    }
}
