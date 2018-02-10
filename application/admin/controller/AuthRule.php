<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/1
 * Time: 13:07
 */
namespace app\admin\controller;

use app\admin\model\AuthRule as RuleModel;
use function input;
use app\admin\controller\Common;
use think\Session;
use function view;
use app\common\controller\Common as Com;
/**
 *
 * @author Administrator
 *         权限规则控制器
 *        
 */
class AuthRule extends Common
{

    /*
     * 展现具体的权限规则
     */
    public function lst()
    {
        $auth = __AUTHRULELST__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        $riles = new RuleModel();
        
        $res_rule = $riles->gettree($riles->getrule());
        // 获取无限极分类
        $this->assign('authRuleRes', $res_rule);
        // 渲染输出
        return view();
    }

    /*
     * 添加权限
     */
    public function add()
    {
        $auth = __AUTHRULEADD__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        if (request()->isPost()) {
            $data = input('post.');
            $plevel = db('auth_rule')->where('id', $data['pid'])
                ->field('level')
                ->find();
            // 查询添加规则上一级的等级,做出以下判断
            if ($plevel) {
                $data['level'] = $plevel['level'] + 1;
            } else {
                $data['level'] = 0;
            }
            $save = db('auth_rule')->insert($data);
            if ($save !== false) {
                $this->success('添加权限成功！', url('lst'));
            } else {
                $this->error('添加权限失败！');
            }
            return;
        }
        $riles = new RuleModel();
        $res_rule = $riles->gettree($riles->getrule());
        // 获取无限极分类
        $this->assign('Resrule', $res_rule);
        // 渲染输出
        return view();
    }

    /*
     * 编辑权限规则
     */
    public function edit($id)
    {
        $auth = __AUTHRULEEDIT__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        if (request()->isPost()) {
            $data = input('post.');
            $plevel = db('auth_rule')->where('id', $data['pid'])
                ->field('level')
                ->find();
            // 查询添加规则上一级的等级,做出以下判断
            if ($plevel) {
                $data['level'] = $plevel['level'] + 1;
            } else {
                $data['level'] = 0;
            }
            $save = db('auth_rule')->update($data);
            if ($save !== false) {
                $this->success('修改权限成功！', url('lst'));
            } else {
                $this->error('修改权限失败！');
            }
            return;
        }
        
        $riles = new RuleModel();
        $one_rule = $riles->find($id);
        $this->assign('authRuleRes', $one_rule);
        $res_rule = $riles->gettree($riles->getrule());
        // 获取无限极分类
        $this->assign('Resrule', $res_rule);
        return view();
    }

    /*
     * 删除规则,包含他下一等级的规则
     */
    public function del()
    {
        $auth = __AUTHRULEDEL__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        $authRule = new RuleModel();
        $authRule->getparentid(input('id'));
        $authRuleIds = $authRule->getchilrenid(input('id'));
        $authRuleIds[] = input('id');
        $del = RuleModel::destroy($authRuleIds);
        if ($del) {
            $this->success('删除权限成功！', url('lst'));
        } else {
            $this->error('删除权限失败！');
        }
    }

}































