<?php
namespace app\index\controller;

use app\common\controller\Common as Com;
use app\index\model\Cate as CateModel;
use app\admin\model\Title as TitleModel;
use think\Controller;
use think\Db;
use think\View;
use const null;
use function array_shift;
use function count;
use function date;
use function time;
use app\index\model\Comment as CommentModel;
use const true;
use think\Request;
class Index extends Controller
{
    public function index()
    {
	    if(Request::instance()->isMobile())
	    {
		     $this->redirect('moblie/index/index');
	    }else{
		    $cate = new CateModel();
		    //仅获取房间类型表的id和类型
		    $type = $cate->field('id,type')->select();
		    //活动推荐
		    $rec = $cate->field('id,type,price,description,thumb,thumb_bed,thumb_bathroom')->where('status',
				    2)->limit(6)->select();
		    /**
		     * 获取推荐的评论,输出到主页
		     */
		    $comment = Db::table('comment')->order('id',
			    'desc')->where('rec=1')->where('status=1')->limit(3)->select();
		    /*
			 *获取活动标题
			 */
		    $title = new TitleModel();
		    $title_info = $title->order('create_time', 'desc')->find();
		    //模板赋值
		    $this->assign('allroom', $type);
		    $this->assign('rec', $rec);
		    $this->assign('title', $title_info);
		    $this->assign('comment', $comment);
		    //渲染输出
		    return $this->fetch();
		
	    }
	    
     
    }

    public function deal()
    {
        $param = $this->request->param();
        $right = $this->validate($param,'Customer.comment');
        if ($right === true) {
            $res = CommentModel::create($param);
            if ($res) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return $right;
        }

    }

    public function book()
    {
        $param = $this->request->param();
        $res   = $this->validate($param,'Customer.ordering');
        if ($res === true) {
            return 1;
        }else{
            return $res;
        }
    }

    /**
     * 搜索结果
     * @return mixed
     * type=0 表示获取全部房型
     */
    public function search(){
        $param = $this->request->param();
        $cate = new CateModel();
        $info= $cate->find($param['type']);
        $start = $info['price'] - 500;
        $end   = $info['price'] + 500;
        /*
         *查询 在用户预订的房间上±500元区间的房子
         */
        $rec_you = $cate->where('price', 'between', [$start, $end])
            ->where('status', '>', 0)
            ->limit(5)
            ->select();
        $com = new Com();
        $rrr = $com->roomStatus($param['check_in'], $param['check_out'], $info['log'], $info['rooms'], $info['num']);
        if (count($rrr) <= 0) {
            $this->error('该时间没有房间了,换一个类型吧');
        }
        //模板赋值
        $this->assign('res_info',$info);
        $this->assign('rec_you', $rec_you);
        return $this->fetch();
    }

    /**
     * @return mixed
     * 渲染房型细节
     */
    public function detail()
    {
        //获取当前房型的id
        $id=$this->request->param('id');
        //根据id信息获取其全部信息
        $cate = new CateModel();
        $room_info = $cate->where('id', $id)
                          ->where('status', '>', 0)
                          ->find();
        $start     =$room_info['price']-500;
        $end       =$room_info['price']+500;
        /*
         *查询 在用户预订的房间上±500元区间的房子
         */
        $rec_you   = $cate->where('price','between',[$start, $end])
                           ->where('status', '>',0)
                           ->limit(6)
                           ->select();
        //模板赋值
        $this->assign('info', $room_info);
        $this->assign('rec_you', $rec_you);
        //模板输出
        return $this->fetch();
    }

    /**
     * @return mixed
     * 展现所有房间信息
     */
    public function allrooms()
    {
        $cate = new CateModel();
        $res = $cate->field('id,price,thumb,type,description,room_service,shuttle,breakfast,laundry,area,depart')
            ->where('status','>=',1)
            ->select();
//        dump($res);
        $this->assign('show',$res);
        return $this->fetch();
    }

    public function about()
    {
        return $this->fetch();
    }
    /**
     * @return mixed
     * 用户填写订单信息
     */
    public function booking()
    {

        $id = $this->request->param('id');

        //获取入住,退房时间
        $checkIn = $this->request->param('check_in');
//        $checkIn = '2017-09-15';
        $checkOut = $this->request->param('check_out');
//        $checkOut = '2017-09-16';

        /**
         * 第一步查询是否有房间,没有直接提示无房间
         * 第二步,从房中弹出一个房间给当前预订的客户
         * 第三步,关于房型传递,采用post
         */
        $cate = new CateModel();
        $res = $cate->where('id',$id)
            ->field('type,price,rooms,log,num')
            ->find();
        $table = $res['log'];
        $count = $res['num'];
        $rooms = $res['rooms'];
        $com = new Com();
        $rrr = $com->roomStatus($checkIn, $checkOut, $table, $rooms, $count);
        if (count($rrr) <= 0) {
            $this->error('对不起,没有房了,换个时间吧');
        }
        $tmp_room = array_shift($rrr);
        if ($tmp_room== null) {
            $this->error('对不起,没有房了,换个时间吧');
        }


        /*
         * 模板赋值
         */
        View::share(['check_in'=>$checkIn,'check_out'=>$checkOut,'tmp_room'=>$tmp_room,'log'=> $table]);
        $this->assign('base',$res);
        return $this->fetch();
    }

    /**
     * @return mixed
     * 返回订单成功界面
     */
    public function ordering()
    {
        $param = $this->request->param();
        $res = Db::table('customer')->insert($param);
        if ($res) {
            $com = new Com();
            $com->writelog($param['check_in'], $param['check_out'], $param['log'], $param['tmp_room']);
        }
        $time = date('YmdHis', time());
        $room_type = $this->request->param('id');
        //订单号
        $order_no = $time . $room_type;
        //生成订单时间
        $burn_time = date('m-d H:i:s', time());
        View::share([
            'order_no'  => $order_no,
            'burn_time' => $burn_time,
            'type'      => $param['type'],
            'check_in'  => $param['check_in'],
            'check_out' => $param['check_out'],
            'name'      => $param['name'],
            'phone'     => $param['phone'],
            'price'     => $param['price'],
        ]);
        return $this->fetch();
    }

}
