<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/17
 * Time: 18:37
 */

namespace app\admin\controller;


use app\common\controller\Common as Com;
use think\Controller;
use think\Db;

class Comment extends Controller
{
    public function lst()
    {
        $auth = __COMMENTLST__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        $comment = Db::table('comment')->paginate(5);
        $this->assign('comment', $comment);
        //渲染输出
        return $this->fetch();
    }

    public function deal()
    {
        $stamp = $this->request->param('stamp');
        $id    = $this->request->param('id');
        if ($stamp === '推荐') {
            $res = Db::table('comment')->update(['rec' => 1, 'id' => $id]);
            if ($res) {
                return 1;
            }else
                return 0;
        }
        if ($stamp === '通过') {
            //该静态方法返回受影响的条数,若无这返回0
            $res = Db::table('comment')->update(['status' => 1,'id'=>$id]);
            if ($res) {
                return 1;
            } else {
                return 0;
            }
        }
    }
}