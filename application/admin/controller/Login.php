<?php

namespace app\admin\controller;

use app\admin\model\User;
use function session;
use think\Controller;
use const true;

class Login extends Controller
{
    /**
     * @return mixed
     * 检测是否重复登入
     * 渲染登入界面
     */
    public function login()
    {
        $this->readyLogin();
        return $this->fetch();
    }

    /**
     * 检测重复登入的方法
     */
    protected function readyLogin()
    {
        if (!empty(session('id')) || !empty(session('username'))) {
            $this->error('您已登录系统,请忽重复登入', url('admin/index'), '', 1);
        }
    }

    /**
     * @return array|int|string|true
     * ajax登入界面请求的处理脚本
     * 返回提示信息或状态码
     */
    public function check_login()
    {
        $param = $this->request->param();
        //内置的拓展函数检测验证码
        if (captcha_check($param['code'])) {
            //调用验证器,指定验证场景
            $res = $this->validate($param, 'Customer.login');
            if ($res === true) {
                $admin = new User();
                $status = $admin->login($param);
                return $status;
            } else {
                //返回提示信息
                return $res;
            }
        } else {
            return 0;
        }
    }

    /**
     * 退出登入,跳转到登入界面
     */
    function logout()
    {
        session(null);
        $this->redirect('login/login');
    }

}