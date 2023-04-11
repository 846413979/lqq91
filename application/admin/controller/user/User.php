<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\library\Auth;
use app\common\model\BrandDiscount;
use think\Db;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;
    protected $searchFields = 'id,username,nickname';

    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('User');
    }

    /**
     * 查看
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
                ->with('group')
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);
            foreach ($list as $k => $v) {
                $v->admin_id = Db::name('admin')->where('id',$v->admin_id)->value('nickname');
                $v->parent_name = $this->model->where('id',$v->pid)->value('username');
                $v->avatar = $v->avatar ? cdnurl($v->avatar, true) : letter_avatar($v->nickname);
                $v->hidden(['password', 'salt']);
            }
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $this->token();
        }
        return parent::add();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        if ($this->request->isPost()) {
            $this->token();
        }
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        return parent::edit($ids);
    }
    //设置客服
    public function kefu($ids)
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
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error($row->getError());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $uids = Db::name('auth_group_access')->where('group_id',2)->column('uid');
        $user = Db::name('admin')->where('id','in',$uids)->where('status','normal')->column('id,nickname');
        $this->assign('user',$user);
        $this->assign('row',$ids);
        return $this->view->fetch();
    }
    //设置免费额度
    public function money($ids)
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
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error($row->getError());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $this->assign('row',$row);
        return $this->view->fetch();
    }
    //设置状态
    public function status($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $params = $this->request->param();

        $result = $row->allowField(true)->save($params);
        if ($result !== false) {
            $this->success();
        } else {
            $this->error($row->getError());
        }
    }

    //设置上级分销账号
    public function parent($ids)
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
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error($row->getError());
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        $this->assign('row',$row);
        return $this->view->fetch();
    }

    //设置品牌折扣
    public function brand($ids)
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
                $model = new BrandDiscount();
                foreach ($event as $v){
                    $find = $model->where(['user_id'=>$params['id'],'brand_id'=>$v['id']])->find();
                    if($find){
                        $model->save(['discount'=>$v['discount']],['user_id'=>$params['id'],'brand_id'=>$v['id']]);
                        $this->cart($params['id'],$v['id'],$v['discount']);
                    }else{
                        $model->save(['user_id'=>$params['id'],'brand_id'=>$v['id'],'discount'=>$v['discount']]);
                        $this->cart($params['id'],$v['id'],$v['discount']);
                    }
                }
                $oldCategoryIds = $model->where(['user_id'=>$params['id']])->column('brand_id');
                $categories        = array_column($event,'id');

                $sameCategoryIds       = array_intersect($categories, $oldCategoryIds);
                $needDeleteCategoryIds = array_diff($oldCategoryIds, $sameCategoryIds);
                Db::name('brand_discount')->where(['user_id'=>$params['id'],'brand_id'=>['in',$needDeleteCategoryIds]])->delete();
                $this->success();
            }

            $this->error(__('Parameter %s can not be empty', ''));
        }

        $user = Db::name('brand_discount')->where('user_id',$ids)->field('brand_id as id,discount')->select();
        $this->assign('id',$ids);
        $this->assign('row',['list'=>json_encode($user,true)]);
        return $this->view->fetch();
    }

    //设置品牌购物车价格改变
    public function cart($user_id,$brand_id,$discount)
    {
        $data = Db::name('cart')->where(['user_id'=>$user_id,'brand_id'=>$brand_id])->select();
        if(!empty($data)){
            foreach ($data as $v){
                $goods_price =  sprintf("%.2f",$v['goods_original_price']*($discount/100)*$v['goods_num']);
                Db::name('cart')->where('id',$v['id'])
                    ->update(['user_brand_discount'=>$discount,'goods_price'=>$goods_price]);
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
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        Auth::instance()->delete($row['id']);
        $this->success();
    }

}
