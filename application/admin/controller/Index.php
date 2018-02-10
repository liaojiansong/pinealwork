<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/14
 * Time: 0:20
 */

namespace app\admin\controller;


use app\admin\model\Cate as CateModel;
use app\common\controller\Common as Com;
use think\Request;
use const DS;
use const ROOT_PATH;
use const true;
use function array_key_exists;


class Index extends Common
{
    public function index()
    {
        return $this->fetch();
    }

    public function listroom()
    {
        $auth = __INDEXLISTROOM__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        $cate = new CateModel();
        $all_info=$cate->field('id,type,price,status')->select();
        $this->assign('allInfo',$all_info);
        return $this->fetch();
    }


    public function addroom()
    {
        $auth = __INDEXADDROOM__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        return $this->fetch();
    }


    /**
     * @param Request $request
     * 处理上传的数据,并存入数据表
     * 比较简陋,还得完善
     */
    public function upload(Request $request)
    {
        $info = $request->param();
        $res  = $this->validate($info,'Customer.upload');
        if ($res===true){
            $file = $request->file();
            $map = ['status', 'room_service', 'breakfast', 'laundry', 'shuttle',];
            $change = explode(',', $info['rooms']);
            //利用count函数统计一共有几个房间号,还要减一
            $info['num'] = (count($change) - 1);
            $info = $this->change($map, $info);
            if (!empty($file)) {
                $name = [];
                //多个文件,所以要遍历
                foreach ($file as $key => $value) {
                    $up_info = $value->move(ROOT_PATH . 'public' . DS . 'upload');
                    $name[$key] = $up_info->getSaveName();
                }
                $info = $info + $name;
                if (!array_key_exists('id', $info)) {
                    $cate = CateModel::create($info);
                } else {
                    $model = new CateModel();
                    $cate = $model->save($info, ['id' => $info['id']]);
                }
                if ($cate) {
                    //上传成功
                    $this->success('操作成功', 'listroom', '', 1);
                } else {
                    $this->error('操作失败');
                }
            } else {
                //文件不存在
                $this->error('请选择上传的图片', 'addroom', '', 1);
            }
        }else{
            $this->error($res);
        }
    }


    public function editroom()
    {
        $auth  = __INDEXEDITROOM__;
        $com = new Com();
        //权限检查
        $com->check_auth($auth);
        $id=$this->request->param('id');
        $one_info=CateModel::get($id);
        $this->assign('one',$one_info);
        return $this->fetch();
    }
    public function deal_room(){
        $id= $this->request->param('id');
        $res = CateModel::destroy($id);
        if ($res) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * @param array $key
     * 一数组的形式传入要转变的键名
     * @param $array
     * 传入要检测的数组
     * @return mixed
     */
    public function change($key, $array)
    {

        foreach ($key as $k => $v) {
            if (array_key_exists($v, $array)) {
                $array[$v] = 1;
            } else {
                $array[$v] = 0;
            }
        }
        return $array;
    }
}