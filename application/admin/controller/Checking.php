<?php

/**

 * Created by PhpStorm.

 * User: Administrator

 * Date: 2017/9/14

 * Time: 20:25

 */



namespace app\admin\controller;





use app\admin\model\Cate as CateModel;

use app\admin\model\Customer as CustomerModel;

use app\common\controller\Common as Com;

use function date;

use function dump;

use function strtotime;

use think\Db;

use const null;

use function time;

use const true;

use function count;

use function implode;

use function round;



class Checking extends Common

{

    /**

     * @return mixed

     * 获取入住名单列表

     */

    public function list()

    {

        $auth = __CHECKINGRESERVATION__;

        $com = new Com();

        //权限检查

        $com->check_auth($auth);

        $customer = new CustomerModel();

        $cus      = $customer->where('status',1)->paginate(5);

        $this->assign('customer', $cus);

        return $this->fetch();

    }

    /**

     * @return mixed

     * 查询余房

     */

    public function remaining()

    {

        $auth = __CHECKINGREMAINING__;

        $com = new Com();

        //权限检查

        $com->check_auth($auth);

        $checkIn = $this->request->param('check_in');

        $checkOut= $this->request->param('check_out');

        //增加一组默认时间,完善程序

        if ($checkIn === null) {

            $checkIn =date('Y-m-d', strtotime('today'));

            $checkOut=date('Y-m-d', strtotime('tomorrow'));

            $this->assign(['check_in'=>$checkIn,'check_out'=>$checkOut]);

        }

        $cate = new CateModel();

        //获取房型的信息

        $info = $cate->field('num,rooms,log,type,price')->select();

        $all_info = [];

        //遍历每一个房型,得出余房信息

        foreach ($info as $key => $val) {

            $rrr = $com->roomStatus($checkIn, $checkOut, $val['log'], $val['rooms'], $val['num']);

            //房间类型

            $alone['type'] = $val['type'];

            //房间价格

            $alone['price'] = $val['price'];

            //房间的总数

            $alone['total'] = $val['num'];

            //如果返回数组为空,就要修改一下返回的数据了

            if ($rrr == null) {

                //因为模板采用了foreach,所以必须给它返回数组

                $rrr = ['已经售罄', '', '', ''];

                $alone['remain'] = $rrr;

                //可预订的房间数

                $alone['remain_num'] = 0;

                //入住率

                $alone['percent'] = '100%';

                //已售=总数

                $alone['sale'] = $alone['total'];

            } else {

                //可预订的房间号

                $alone['remain'] = $rrr;

                //可预订的房间数

                $alone['remain_num'] = count($rrr);

                //已售

                $alone['sale'] = ($alone['total'] - $alone['remain_num']);

                //入住率

                $alone['percent'] = round(($alone['sale'] / $alone['total']) * 100, 2) . '%';

            }

            $all_info[] = $alone;

        }

        $this->assign('reroom', $all_info);

        return $this->fetch();

    }



    /**

     * @return array

     * 查询余房的ajax方法的脚本

     */

    public function check_remain()

    {

        $checkIn = $this->request->param('check_in');

        $checkOut = $this->request->param('check_out');

        $cate = new CateModel();

        //实例化公共类

        $com = new Com();

        //获取房型的信息

        $info = $cate->field('num,rooms,log,type,price')->select();

        $all_info = [];

        //遍历每一个房型,得出余房信息

        foreach ($info as $key => $val) {

            $rrr = $com->roomStatus($checkIn, $checkOut, $val['log'], $val['rooms'], $val['num']);

            //房间类型

            $alone['type'] = $val['type'];

            //房间价格

            $alone['price'] = $val['price'];

            //房间的总数

            $alone['total'] = $val['num'];

            //如果返回数组为空,就要修改一下返回的数据了

            if ($rrr == null) {

                $alone['remain'] = '已经售罄';

                //可预订的房间数

                $alone['remain_num'] = 0;

                //入住率

                $alone['percent'] = '100%';

                //已售=总数

                $alone['sale'] = $alone['total'];

            } else {

                //可预订的房间数

                $alone['remain_num'] = count($rrr);

                //可预订的房间号

                $alone['remain'] = implode(',', $rrr);

                //已售

                $alone['sale'] = ($alone['total'] - $alone['remain_num']);

                //入住率

                $alone['percent'] = round(($alone['sale'] / $alone['total']) * 100, 2) . '%';

            }

            $all_info[] = $alone;

        }

        return $all_info;

    }



    /**

     * 预约入住

     * 查询预订的用户信息,渲染模板

     */

    public function reservation()

    {

        $auth = __CHECKINGRESERVATION__;

        $com = new Com();

        //权限检查

        $com->check_auth($auth);

        $customer = new CustomerModel();

        $cus = $customer->where('status', 0)->paginate(5);

        $this->assign('customer', $cus);

        return $this->fetch();

    }



    /**

     * @return mixed

     * 获取房型信息,渲染到店订购模板

     */

    public function buyNow()

    {

        $auth = __CHECKINGBUYNOW__;

        $com = new Com();

        //权限检查

        $com->check_auth($auth);

        $cate = new CateModel();

        $res  = $cate->field('id,type,price,num,rooms')->select();

        $this->assign('room_type',$res);

        return $this->fetch();

    }



    /**

     * @return array|false|int|\PDOStatement|string|\think\Model|true|void

     * 专业处理各个ajax的请求

     */

    public function inquire()

    {

        //获取标志位,按需处理

        $stamp = $this->request->param('stamp');

        $param = $this->request->param();

        /*

         * 到店预订响应脚本

         */

        if ($stamp === 'buy') {

            $result = $this->validate($param, 'Customer.ordering');

            if ($result === true) {

                $info = Db::table('cate')->field('type,price,log')->find($param['type']);

                //拼装数据

                $param['price'] = $info['price'];

                $param['type'] = $info['type'];

                $param['status'] = 1;

                $param['tmp_room'] = $param['tmp_room'] . ',';

                $customer = new CustomerModel();

                $write = $customer->allowField(true)->save($param);

                if ($write) {

                    $com = new Com();

                    $com->writelog($param['check_in'], $param['check_out'], $info['log'], $param['tmp_room']);

                    //订购成功,返回1

                    return 1;

                } else {

                    //订购失败,返回0

                    return 0;

                }

            } else {

                //数据有问题,返回提示信息

                return $result;

            }

        }

        /*

         * 到店订购,点我检测响应脚本

         * 查询某个房型的余房,返回一个房间号数组

         */

        if ($stamp==='getCate'){

            $param = $this->request->param();

            $cate  = new  CateModel();

            $res   = $cate->field('log,rooms,num')->where('id',$param['type'])->find();

            $com = new Com();

            $rrr= $com->roomStatus($param['check_in'], $param['check_out'], $res['log'], $res['rooms'], $res['num']);

            if ($rrr === []) {

                $rrr=['已经售罄'];
            }

            return $rrr;

        }

        /*

         * 处理预约入住

         */

        if ($stamp == '入住') {

            $res =Db::table('customer')->update(['status'=>1,'id'=>$param['id']]);

            return $res;

        }

        /*

         * 处理退房

         */

        if ($stamp == 'out') {

            $res = Db::table('customer')->update(['status' => 2, 'id' => $param['id']]);

            return $res;

        }

    }



    /**

     * @return array|false|int|\PDOStatement|string|\think\Model

     */

    public function deal()

    {

        $param=$this->request->param();

        $stamp=$param['stamp'];

        $keyword = $param['name'];

        $ser = new  CustomerModel();

        /*

         * 预约入住响应脚本

         * 查询某个用户是否在预订列表中

         */

        if ($stamp === 'reservation') {

            $cus = $ser->where('status',0)->where('name', 'like', '%' . $keyword . '%')->find();

            if ($cus == null) {

                return $cus = 0;

            }

            return $cus;

        }

        if ($stamp === 'list') {

            $cus = $ser->where('status', 1)->where('name', 'like', '%' . $keyword . '%')->find();

            if ($cus == null) {

                return $cus = 0;

            }

            return $cus;

        }

    }



}