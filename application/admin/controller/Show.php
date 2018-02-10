<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/23
 * Time: 0:56
 */

namespace app\admin\controller;
use app\admin\controller\Common;
use app\common\controller\Common as Com;
use app\admin\model\Cate as CateModel;
use app\admin\model\Title as TitleModel;
use think\Db;

class Show extends Common
{
    public function edit()
    {
        $auth = __INDEXLISTROOM__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        $cate        =  new CateModel();
        $all_info    =  $cate->field('id,type,price,status')->select();
        $title       =  new TitleModel();
        $title_info  =  $title->order('create_time','desc')->find();
        $this->assign('title',$title_info);
        $this->assign('allInfo', $all_info);
        return $this->fetch();
    }

    public function deal()
    {
        $param = $this->request->param();
        $stamp = $param['stamp'];
        if ($stamp === 'title') {
            $res = TitleModel::create($param);
            if ($res) {
                return 1;
            } else {
                return 0;
            }
        }
        if ($stamp === '推荐') {
            $res = Db::table('cate')->update(['status'=>2,'id'=>$param['id']]);
            if ($res) {
                return 1;
            } else {
                return 0;
            }
        }
        if ($stamp === '取消推荐') {
            $res = Db::table('cate')->update(['status' => 1, 'id' => $param['id']]);
            if ($res) {
                return 1;
            } else {
                return 0;
            }
        }

    }

}