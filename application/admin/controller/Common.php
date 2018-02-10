<?php

namespace app\admin\controller;
use think\Controller;

/**
 * Class Common
 * @package app\admin\controller
 * 前置操作
 */
class Common extends Controller
{
    /**
     * 前置操作,检测用户是否登入
     */
    protected function _initialize()
    {
        if (!session('id') || !session('username')) {
            $this->error('您尚未登录系统,正在转到登入界面', url('login/login'));
        }
    }
}