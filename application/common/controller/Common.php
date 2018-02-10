<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/21
 * Time: 10:07
 */

namespace app\common\controller;


use function dump;
use think\Controller;
use think\Db;
use think\Session;

class Common extends Controller
{
    /**
     * @param $auth
     * 权限检测
     */
    public   function check_auth($auth)
    {
        $ru = Session::get('rule');
        $rule = explode(",", $ru);
        if (!in_array($auth, $rule)) {
            $this->error('无权访问,请联系管理员');
        }
    }

    /**
     * @param $checkIn
     * 查询的开始时间
     * @param $checkOut
     * 查询的结束时间
     * @param $table
     * 对应房型的日志表
     * @param $rooms
     * 该房型的具体标号
     * @param $count
     * 该房型的数量
     * @return array|void
     * 返回空闲的房间
     */
    public function roomStatus($checkIn, $checkOut, $table, $rooms, $count)
    {
        /*
         * 获取该房型预订日志
         * 利用between区间查询
         */
        //格式开始时间为年的第几天
        $start = date('z', strtotime($checkIn));
        $end = date('z', strtotime($checkOut));
        $room_info = Db::table($table)->where('day', 'between', [$start, $end])->select();
        //定义空字符串接收遍历结果
        $str = '';
        /**
         * 首先判断每一天的房间数是否小于总房间数,小于表示该时间段有房可定
         * 如果大于或等于就之间返回无房可定
         */
        foreach ($room_info as $key => $value) {
            //判断房间数是否符合要求
            if ($value['count'] >= $count) {
                return;
            } else {
                //符合要求,拼接字符串
                $str .= $value['room'];
            }
        }
        $aaa = explode(',', $str);
        $bbb = explode(',', $rooms);
        $check = array_unique($aaa);
        //比较的顺序很重要,多的在前面
        $res = array_diff($bbb, $check);
        $row =[];
        foreach ($res as $k => $v) {
            $row[]= $v;
        }

        return $row;
    }

    public function test()
    {
        $res=$this->roomStatus('2017-09-25', '2017-09-26', 'a_log', '101,102,103,104,105,106,', 6);
        $rrr=$this->roomStatus('2017-09-27', '2017-09-28', 'a_log', '101,102,103,104,105,106,', 6);
        dump($res);
        dump($rrr);
    }

    /**
     * @param $checkIn
     * 开始写入的时间
     * @param $checkOut
     * 结束写入的时间
     * @param $table
     * 对应的日志表
     * @param $tmp_room
     * 要写入的房间号
     */
    public function writelog($checkIn, $checkOut, $table, $tmp_room)
    {
        $start = date('z', strtotime($checkIn));
        $end = date('z', strtotime($checkOut));
        $room_info = Db::table($table)->where('day', 'between', [$start, $end])->select();

        foreach ($room_info as $value) {
            $value['room'] .= $tmp_room;
            //更新表的数据
            Db::table($table)->update(['room' => $value['room'], 'id' => $value['id']]);

        }
    }

    /**
     * @param $start
     * 起始时间
     * @param $end
     * 结束时间
     * @return array
     * 返回开始时间和结束时间的时间戳数组
     */
    public function change_time($start, $end)
    {
        $start = date('z', strtotime($start));
        $end = date('z', strtotime($end));
        return $map_time = ['start' => $start, 'end' => $end];
    }
}