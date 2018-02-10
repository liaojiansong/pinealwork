<?php

namespace app\admin\controller;

use app\admin\model\User;
use app\common\controller\Common as Com;
use think\Db;
use think\Loader;
use function input;
use function md5;

class Admin extends Common
{

    // 渲染后台主页
    public function index()
    {
        return $this->fetch('index/index');
    }

    // 后台管理员页面展示
    public function lst()
    {
        $auth = __ADMINLST__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        $admin = new User();
        $res_admin = $admin->see();
        // 调用user模型的see方法
        $this->assign('adminres', $res_admin);
        // 渲染输出
        return view();
    }

    /*
     * 添加管理员的方法
     */
    public function add()
    {
        $auth = __ADMINADD__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        if (request()->isPost()) {
            $data = input('post.');
            $valiadte = Loader::validate('User');
            // 加载验证器
            if (!$valiadte->scene('add')->check($data)) {
                $this->error($valiadte->getError());
            }
            $admin = new User();
            if ($admin->addadmin($data)) {
                $this->success('添加成功', url('lst'));
            } else {
                $this->error('添加管理员失败');
            }
        }
        $groupRes = db('auth_group')->field('group_id,title')->select();
        // 查询角色表,渲染输出
        $this->assign('group', $groupRes);
        return view();

    }

    /*
     * 编辑管理员
     */
    public function edit()
    {
        $auth = __ADMINEDIT__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        $param = $this->request->param();
        //获取分组信息.渲染下拉列表
        $group = Db::table('auth_group')->field('group_id,title')->select();
        $one_info = User::get($param['user_id']);
        $this->assign('group', $group);
        $this->assign('user', $one_info);
        return $this->fetch();
    }

    public function deal()
    {
        $param = $this->request->param();
        $validate = Loader::validate('User');
        if (!$validate->scene('edit')->check($param)) {
            $this->error($validate->getError());
        } else {
            $data['username'] = $param['username'];
            $data['password'] = md5($param['password']);
            $res = User::update($data, ['id' => $param['user_id']]);
            $rrr = Db::table('auth_group_access')->where('uid',
                $param['user_id'])->update(['group_id' => $param['group_id']]);
            if ($res && $rrr) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }

        }
    }

    /*
     * 删除管理员(软删除),需要在模型中加载软删除的trait
     */
    public function delete($user_id)
    {
        $auth = __ADMINDEL__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        if (User::destroy($user_id)) {
            Db::table('auth_group_access')->where('uid', $user_id)->update([
                'status' => 0
            ]);
            $this->success('删除管理员成功！', url('lst'));
        }
    }

    // 视图查询
    // public function see()
    // {
    // return $res = Db::view('user', 'id,username')->view('auth_group_access', 'uid,group_id', 'auth_group_access.uid=user.id', 'LEFT')
    // ->view('auth_group', 'title', 'auth_group.group_id=auth_group_access.group_id')
    // ->select();
    // }
    // SELECT User.id,User.name,Profile.truename,Profile.phone,Profile.email,Score.score FROM think_user User
    // INNER JOIN think_profile Profile ON Profile.user_id=User.id INNER JOIN think_socre Score ON Score.user_id=Profile.id WHERE Score.score > 80
}












