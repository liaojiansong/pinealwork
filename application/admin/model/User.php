<?php
namespace app\admin\Model;

use function session;
use think\Controller;
use think\Session;
use \traits\model\SoftDelete;
use think\Db;
use think\Model;

class User extends Model
{
    // 软删除,要在数据表中添加delete_time 字段,默认为空
    use SoftDelete;

    protected $deleteTime = 'delete_time';

    /*
     * 检查用户是否存在,检测密码是否正确,都对返回true
     */
    public function login($data)
    {
        //获取用户的信息
        $user = User::getByUsername($data['username']);

        // 动态查询,返回一个用户结果集或者null
        if ($user) {
            if ($user['status'] == 1) {
                if ($user['password'] == md5($data['password'])) {
                    $title = Db::view('auth_group_access', '*')
                        ->view('auth_group', '*', 'auth_group_access.group_id=auth_group.group_id')
                        ->where('uid', $user['id'])
                        ->find();
                    if ($title['status'] == 1) {
                        session('username', $data['username']);
                        // 将结果保存到session中
                        session('id', $user['id']);
                        session('title', $title['title']);
                        // 提供给头部欢迎词使用
                        session('rule', $title['rule']);
                        // 查询登入用户拥有的权限提供给进行左边栏使用
                        return 5;//用户状态正常
                    } else {
                        return 4;// 用户存在且密码正确但是角色被禁用
                    }
                } else {
                    return 3;//密码错误
                }
            } else {
                return 2; // 用户已被禁用
            }
        } else {
            return 1; // 用户不存在
        }
    }

    /*
     * 获取管理员
     */
    public function getadmin()
    {
        return $this->paginate(5, false, [
            'var_page' => 'page'
        ]);
    }

    /*
     * 保存管理员
     */
    public function addadmin($data)
    {
        // 判断是否为空,是否是数组
        if ($data['password']) {
            $data['password'] = md5($data['password']);
            
            $dataArray = array();
            // 进行转换过滤
            $dataArray['password'] = $data['password'];
            $dataArray['username'] = $data['username'];
            if ($this->save($dataArray)) {
                $group_access['uid'] = $this->id;
                $group_access['group_id'] = $data['group_id'];
                
                if (db('auth_group_access')->insert($group_access)) {
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    /*
     * 更新管理员
     * 修改用户在角色表中对应的角色
     */
    public function updateadmin($data)
    {
        // 判断是否为空,是否是数组
        if ($data['password']) {
            $data['password'] = md5($data['password']);
            
            $dataArray = array();
            $dataArray['password'] = $data['password'];
            $dataArray['username'] = $data['username'];
            if ($this->save($dataArray, [
                'id' => $data['user_id']
            ])) {
                $group_access['uid'] = $data['user_id'];
                $group_access['group_id'] = $data['group_id'];
                
                if (Db::table('auth_group_access')->where('uid', $data['user_id'])->setField('group_id', $data['group_id'])) {
                    // 修改用户在角色表中对应的角色
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    /*
     * 视图查询,王者典范,哈哈哈
     * 查询当前全部 全部 全部用户各自对应的角色名称
     */
    public function see()
    {
        /*
         * 1 查询用户表的id username
         * 2 查询中间表的UID 和groupid 条件:中间表的uid =用户表的id
         * 3 查询角色表的title 条件:角色表的groupid = 中间表的groupid
         * 4 条件 delete_time字段为空的用户(软删除的用户delete_time不为空)
         * 5 分页输出
         */
        return $res = Db::view('user', 'id,username')->view('auth_group_access', 'uid,group_id', 'auth_group_access.uid=user.id')
            ->view('auth_group', 'title', 'auth_group.group_id=auth_group_access.group_id')
            ->where('delete_time', 'null')
            ->order('id asc')
            ->paginate(5);
    }

    /*
     * 用当前用户的session信息查询他拥有的权限的具体信息结果集
     */
    public function sss()
    {
        $data = Session::get('rule');
        
        $sql = "SELECT * FROM auth_rule WHERE id IN ($data)";
        // 原生查询
        $res = Db::query($sql);
        return $res;
        // 返回结果集
    }

    /*
     * 递归函数,为渲染左边栏提供条件
     */
    public function decate($data, $pid = 0, $level = 0)
    {
        static $arr = array();
        foreach ($data as $k => $v) {
            if ($v['id'] == $pid) {
                $v['level'] == $level;
                $arr[] = $v;
                $this->decate($data, $v['id'], $level = 1);
            }
        }
        return $arr;
    }
}

