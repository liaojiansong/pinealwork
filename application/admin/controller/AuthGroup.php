<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/1
 * Time: 0:57
 */
namespace app\admin\controller;

use app\admin\model\AuthGroup as GroupModel;
use app\admin\model\AuthRule as RuleModel;
use app\admin\controller\Common;
use function implode;
use function input;
use app\common\controller\Common as Com;

/**
 *
 * @author Administrator
 *         @角色表的类
 *        
 */
class AuthGroup extends Common
{

    /*
     * 过去当前的角色表,用于渲染输出
     */
    public function lst()
    {
        $auth = __AUTHGROUPLST__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        $group = GroupModel::all();

        $this->assign('groupshow', $group);
        
        return view();
    }

    /**
     * 编辑角色
     *  @param group_id int 需要编辑的角色的id
     */
    public function edit($group_id)
    {
        $auth = __AUTHGROUPEDIT__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        if (request()->isPost()) {
            $data = input('post.');

            $data['rule'] = implode(',', $data['rules']);
            // 传递上来的是数组,将其转化为字符串
            if (! array_key_exists('status', $data)) {
                // 判断数组中是否存在某个字段
                $data['status'] = '0';
                // 当单选框不被选中是,status不会提交
            }

            $group = new GroupModel();
            if ($group->allowField(true)->save($data, [
                'group_id' => $group_id
            ])) { // 执行更新
                $this->success('修改角色成功', url('AuthGroup/lst'));
            } else {
                $this->error('修改角色失败');
            }
        }
        $group_one = GroupModel::get($group_id);
        // 获取角色表的单条信息
        $Rulemodel = new RuleModel();
        $rule_res = $Rulemodel->gettree($Rulemodel->getrule());
         //获取具体规则的无限极分类
        $this->assign('AuthRes', $rule_res);
        $this->assign('groupone', $group_one);
        return view();
    }

}