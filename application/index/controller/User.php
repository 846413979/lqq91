<?php

namespace app\index\controller;

use addons\wechat\model\WechatCaptcha;
use app\common\controller\Frontend;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\model\Attachment;
use think\Config;
use think\Cookie;
use think\Db;
use think\Hook;
use think\Session;
use think\Validate;

/**
 * 会员中心
 */
class User extends Frontend
{
//    protected $layout = 'default';
    protected $noNeedLogin = ['login', 'register', 'third','user_check'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $auth = $this->auth;

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'), '/');
        }

        //监听注册登录退出的事件
        Hook::add('user_login_successed', function ($user) use ($auth) {
            $expire = input('post.keeplogin') ? 30 * 86400 : 0;
            Cookie::set('uid', $user->id, $expire);
            Cookie::set('token', $auth->getToken(), $expire);
        });
        Hook::add('user_register_successed', function ($user) use ($auth) {
            Cookie::set('uid', $user->id);
            Cookie::set('token', $auth->getToken());
        });
        Hook::add('user_delete_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
        Hook::add('user_logout_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $this->view->assign('title', __('User center'));
        return $this->view->fetch();
    }

    public function user_check()
    {
        $param = $this->request->param();
        $res = \app\common\model\User::get(['username' => $param['username']]);
        if(!empty($res)){
            $this->error('');
        }else{
            $this->success('');
        }
    }

    /**
     * 注册会员
     */
    public function register()
    {
        $url = $this->request->request('url', '', 'trim');
        if ($this->auth->id) {
            $this->success(__('You\'ve logged in, do not login again'), $url ? $url : url('user/index'));
        }
        if ($this->request->isPost()) {
            $param = $this->request->param();
            $data = $param['data'];

//            $username = $this->request->post('username');
//            $password = $this->request->post('password');
//            $email = $this->request->post('email');
//            $mobile = $this->request->post('mobile', '');
//            $captcha = $this->request->post('captcha');
//            $token = $this->request->post('__token__');
            $rule = [
                'username'  => 'require|length:3,30|unique:user',
                'password'  => 'require|length:6,30|confirm',
                'password_confirm'=>'require|confirm:password',
                'email'     => 'require|email',
                'mobile'    => 'regex:/^1\d{10}$/|unique:mobile',
                '__token__' => 'require|token',
            ];

            $msg = [
                'username.require' => '用户名不能为空',
                'username.length'  => '用户名必须在3到30之间',
                'username.unique'  => '用户名已存在',
                'password.require' => '密码不能为空',
                'password.length'  => '密码必须6到30之间',
                'password.confirm'  => '两次密码不一致',
                'password_confirm.length'  => '确认密码不能为空',
                'password_confirm.confirm' => '两次密码不一致',
                'email.confirm'   => '邮箱不能为空',
                'email.email'     => '邮箱格式不正确',
                'mobile.length'   => '手机号不能为空',
                'mobile.unique'   => '手机号已存在',
            ];
//            $data = [
//                'username'  => $username,
//                'password'  => $password,
//                'email'     => $email,
//                'mobile'    => $mobile,
//                '__token__' => $token,
//            ];


            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);

            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
            }
            halt(1);
            if ($this->auth->register($username, $password, $email, $mobile)) {
                $this->success(__('Sign up successful'), $url ? $url : url('user/index'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('captchaType', config('fastadmin.user_register_captcha'));
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Register'));
        //专属客服
        $uid = Db::name('auth_group_access')->where('group_id',2)->column('uid');
        $admin = Db::name('admin')->where('id','in',$uid)->field('id,nickname')->select();
        $this->view->assign('admin', $admin);
        return $this->view->fetch();
    }

    /**
     * 会员登录
     */
    public function login()
    {
        $url = $this->request->request('url', '', 'trim');
        if ($this->auth->id) {
            $this->success(__('You\'ve logged in, do not login again'), $url ? $url : url('user/index'));
        }
        if ($this->request->isPost()) {

            $account = $this->request->post('account');
            $password = $this->request->post('password');
            $keeplogin = (int)$this->request->post('keeplogin');
            $token = $this->request->post('__token__');
            $rule = [
                'account'   => 'require|length:3,50',
                'password'  => 'require|length:6,30',
                '__token__' => 'require|token',
            ];

            $msg = [
                'account.require'  => 'Account can not be empty',
                'account.length'   => 'Account must be 3 to 50 characters',
                'password.require' => 'Password can not be empty',
                'password.length'  => 'Password must be 6 to 30 characters',
            ];
            $data = [
                'account'   => $account,
                'password'  => $password,
                '__token__' => $token,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
                return false;
            }
            if ($this->auth->login($account, $password)) {
                $this->success(__('Logged in successful'), $url ? $url : url('user/index'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Login'));
        return $this->view->fetch();
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        if ($this->request->isPost()) {
            $this->token();
            //退出本站
            $this->auth->logout();
            $this->success(__('Logout successful'), url('user/index'));
        }
        $html = "<form id='logout_submit' name='logout_submit' action='' method='post'>" . token() . "<input type='submit' value='ok' style='display:none;'></form>";
        $html .= "<script>document.forms['logout_submit'].submit();</script>";

        return $html;
    }

    /**
     * 个人信息
     */
    public function profile()
    {
        $this->view->assign('title', __('Profile'));
        return $this->view->fetch();
    }

    /**
     * 修改密码
     */
    public function changepwd()
    {
        if ($this->request->isPost()) {
            $oldpassword = $this->request->post("oldpassword");
            $newpassword = $this->request->post("newpassword");
            $renewpassword = $this->request->post("renewpassword");
            $token = $this->request->post('__token__');
            $rule = [
                'oldpassword'   => 'require|regex:\S{6,30}',
                'newpassword'   => 'require|regex:\S{6,30}',
                'renewpassword' => 'require|regex:\S{6,30}|confirm:newpassword',
                '__token__'     => 'token',
            ];

            $msg = [
                'renewpassword.confirm' => __('Password and confirm password don\'t match')
            ];
            $data = [
                'oldpassword'   => $oldpassword,
                'newpassword'   => $newpassword,
                'renewpassword' => $renewpassword,
                '__token__'     => $token,
            ];
            $field = [
                'oldpassword'   => __('Old password'),
                'newpassword'   => __('New password'),
                'renewpassword' => __('Renew password')
            ];
            $validate = new Validate($rule, $msg, $field);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
                return false;
            }

            $ret = $this->auth->changepwd($newpassword, $oldpassword);
            if ($ret) {
                $this->success(__('Reset password successful'), url('user/login'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        $this->view->assign('title', __('Change password'));
        return $this->view->fetch();
    }

    public function attachment()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $mimetypeQuery = [];
            $where = [];
            $filter = $this->request->request('filter');
            $filterArr = (array)json_decode($filter, true);
            if (isset($filterArr['mimetype']) && preg_match("/(\/|\,|\*)/", $filterArr['mimetype'])) {
                $this->request->get(['filter' => json_encode(array_diff_key($filterArr, ['mimetype' => '']))]);
                $mimetypeQuery = function ($query) use ($filterArr) {
                    $mimetypeArr = array_filter(explode(',', $filterArr['mimetype']));
                    foreach ($mimetypeArr as $index => $item) {
                        $query->whereOr('mimetype', 'like', '%' . str_replace("/*", "/", $item) . '%');
                    }
                };
            } elseif (isset($filterArr['mimetype'])) {
                $where['mimetype'] = ['like', '%' . $filterArr['mimetype'] . '%'];
            }

            if (isset($filterArr['filename'])) {
                $where['filename'] = ['like', '%' . $filterArr['filename'] . '%'];
            }

            if (isset($filterArr['createtime'])) {
                $timeArr = explode(' - ', $filterArr['createtime']);
                $where['createtime'] = ['between', [strtotime($timeArr[0]), strtotime($timeArr[1])]];
            }
            $search = $this->request->get('search');
            if ($search) {
                $where['filename'] = ['like', '%' . $search . '%'];
            }

            $model = new Attachment();
            $offset = $this->request->get("offset", 0);
            $limit = $this->request->get("limit", 0);
            $total = $model
                ->where($where)
                ->where($mimetypeQuery)
                ->where('user_id', $this->auth->id)
                ->order("id", "DESC")
                ->count();

            $list = $model
                ->where($where)
                ->where($mimetypeQuery)
                ->where('user_id', $this->auth->id)
                ->order("id", "DESC")
                ->limit($offset, $limit)
                ->select();
            $cdnurl = preg_replace("/\/(\w+)\.php$/i", '', $this->request->root());
            foreach ($list as $k => &$v) {
                $v['fullurl'] = ($v['storage'] == 'local' ? $cdnurl : $this->view->config['upload']['cdnurl']) . $v['url'];
            }
            unset($v);
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $mimetype = $this->request->get('mimetype', '');
        $mimetype = substr($mimetype, -1) === '/' ? $mimetype . '*' : $mimetype;
        $this->view->assign('mimetype', $mimetype);
        $this->view->assign("mimetypeList", \app\common\model\Attachment::getMimetypeList());
        return $this->view->fetch();
    }
}
