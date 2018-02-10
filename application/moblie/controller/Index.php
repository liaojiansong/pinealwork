<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/14
 * Time: 20:04
 */

namespace app\moblie\controller;

use app\admin\model\Record;
use app\common\controller\Common as Com;
use app\index\model\Cate as CateModel;
use app\moblie\model\Consumer;
use function array_count_values;
use function array_key_exists;
use function date;
use function dump;
use function in_array;
use function print_r;
use think\Controller;
use think\Db;
use think\Session;
use function time;
use const true;
use function count;
use function md5;

class Index extends Controller
{
	/**
	 * @return mixed
	 * 渲染主页
	 */
	public function index()
	{
		$cate = new CateModel();
		//仅获取房间类型表的id和类型
		$type = $cate->field('id,type')->select();
		//模板赋值
		if(Session::has('user_info') || Session::has('wetchat'))
		{
			$type['status'] = 1;
		}else{
			$type['status'] = 0;
		}
		
		$this->assign('allroom', $type);

		//渲染输出
		return $this->fetch();
		
	}
	
	/**
	 * @return mixed
	 * 渲染主页
	 */
	public function login()
	{
		return $this->fetch();
	}
	
	/**
	 * 登入检查
	 */
	public function loginCheck()
	{
		$userInfo = $this->request->post();
		$consumer = new Consumer();
		$res = $consumer->login($userInfo);
		if($res===1){
			$this->error('用户不存在');
		} elseif($res===2)
		{
			$this->error('用户已被禁用');
		} elseif($res===3)
		{
			$this->error('密码错误');
		} elseif($res===4)
		{
			$this->redirect('userinfo');
		}
	}
	
	/**
	 * 退出登入
	 */
	function logout()
	{
		//置空session
		session(null);
		$this->redirect('index');
	}
	
	/**
	 * @param $id
	 * 房型ID
	 * @param $checkIn
	 * 入住时间
	 * @param $checkOut
	 * 离店时间
	 * @return array|int
	 * 失败返回状态码,成功返回余房数据
	 */
	public function check($id, $checkIn, $checkOut)
	{
		/**
		 * 第一步查询是否有房间,没有直接提示无房间
		 * 第二步,从房中弹出一个房间给当前预订的客户
		 * 第三步,关于房型传递,采用post
		 */
		$cate = new CateModel();
		$res = $cate->where('id', $id)->field('type,price,rooms,log,num')->find();
		$table = $res['log'];
		$count = $res['num'];
		$rooms = $res['rooms'];
		$com = new Com();
		//返回余房数组
		$rrr = $com->roomStatus($checkIn, $checkOut, $table, $rooms, $count);
		
		if(count($rrr)<=0)
		{
			return 400;
			//$this->error('对不起,没有房了,换个时间吧');
		}else{
			//从数组头弹出一个房间
			$tmp_room = array_shift($rrr);
			//二级判断
			if($tmp_room==null)
			{
				return 400;
			}else{
				$baseInfo = [
					'tmp_room'  => $tmp_room,
					'log'       => $table,
					'price'     => $res['price'],
					'type'      => $res['type'],
				];
				return $baseInfo;
			}
		}
		
	}
	
	/**
	 * @return mixed
	 * 写入数据库
	 */
	public function booking()
	{
		
		$param = $this->request->post();
		//dump($param);
		//数据验证
		$msg = $this->validate($param, 'Customer.ordering');
		//dump($msg);
		if($msg!==true)
		{
			$this->error($msg);
		}
		//查询余房,返回400或余房数组
		$flag = $this->check($param['room_id'],$param['check_in'],$param['check_out']);
		if($flag === 400){
			$this->error('对不起,没有房了,换个时间吧');
		}else{
			$user_id = Session::get('user_info');
			$data = [
				'room'   =>   $flag ['tmp_room'],
				'type'   =>   $flag ['type'],
				'price'  =>   $flag ['price'],
				'name'   =>   $param['name'],
				'phone'  =>   $param['phone'],
				'id_card'=>   $param['id_card'],
				'check_in'=>  $param['check_in'],
				'check_out'=> $param['check_out'],
				'time'   =>   time(),
				'order_number'   =>   time(),
				'user_id'=>   $user_id['id'],
			];
		
			$record = new Record();
			$record->data($data)->allowField(true)->save();
			$save_id = $record->id;
			if($save_id){
				$com = new Com();
				$tmp_room = $flag['tmp_room'].',';
				$com->writelog($param['check_in'], $param['check_out'], $flag['log'], $tmp_room );
				$single = 200;
				$this->assign('single', $single);
				return $this->fetch('tip');
			} else
			{
				$this->error('预订失败');
			}
		
		}
		
	}
	
	/**
	 * @return mixed
	 * 渲染个人主页
	 * 订单概况
	 */
	public function userinfo()
	{
		$userinfo = Session::get('user_info');
		//根据用户的ID查询消费记录
		$user_id = $userinfo['id'];
		//单纯获取某一列
		$info = Record::where('user_id',$user_id)->column('status');
		$total= count($info);
		//以值=>次数的形式返回
		$mixed= array_count_values($info);
		$mixedInfo = [];
		$mixedInfo['total'] = $total;
		if(array_key_exists(1, $mixed))
		{
			$mixedInfo['stay'] = $mixed[1];
		}else{
			$mixedInfo['stay'] = 0;
		}
		if(array_key_exists(2, $mixed))
		{
			$mixedInfo['done'] = $mixed[2];
		}else{
			$mixedInfo['done'] = 0;
		}
		if(array_key_exists(3, $mixed))
		{
			$mixedInfo['expired'] = $mixed[3];
		}else{
			$mixedInfo['expired'] = 0;
		}
		$this->assign('mixed',$mixedInfo);
		$this->assign('userinfo',$userinfo);
		return $this->fetch();
	}
	
	public function done()
	{
		$flag     = $this->request->param('flag');
		$userinfo = Session::get('user_info');
		//根据用户的ID查询消费记录
		$user_id = $userinfo['id'];
		if($flag==0)
		{
			$list = Record::all(function ($query) use ($user_id)
			{
				$query->where('user_id', $user_id);
			});
		}else{
			$list = Record::all(function ($query) use ($user_id, $flag)
			{
				$query->where('status', $flag)->where('user_id', $user_id);
			});
		}
		if(empty($list)){
			$this->redirect('userinfo');
		}
		$list[0]['time'] = date('Y-m-d H:i:s', $list[0]['time']);
		$this->assign('flag',$flag);
		$this->assign('record',$list);
		return $this->fetch();
	}
	
	
	public function standby()
	{
		$userinfo = Session::get('user_info');
		//根据用户的ID查询消费记录
		$user_id = $userinfo['id'];
		$record = new Record();
		$info = $record->where('user_id', $user_id)->select();
	}
	
	/**
	 * @return mixed
	 * 渲染注册界面
	 */
	public function reg()
	{
		return $this->fetch();
	}
	
	/**
	 * 处理注册信息
	 */
	public function regdo()
	{
		$info = $this->request->post();
		$res = $this->validate($info,'Customer.reg');
		
		if($res!==true)
		{
			$this->error($res);
		}
		if($info['password']!=$info['comfir_password'])
		{
			$this->error($res = '两次密码不一致') ;
		}else{
			$info['password'] = md5($info['password']);
			$consumer = new Consumer();
			$consumer->data($info)->allowField(true)->save();
			if($consumer->id){
				$this->success('注册成功,快去登入吧','login');
			}else{
				$this->error('注册失败');
			}
		}
	}
	
	/**
	 * @return mixed
	 * 用户查询
	 */
	public function search()
	{
		$param = $this->request->param();
		$cate = new CateModel();
		$info = $cate->find($param['type']);
		$start = $info['price'] - 500;
		$end = $info['price'] + 500;
		/*
		 *查询 在用户预订的房间上±500元区间的房子
		 */
		$rec_you = $cate->where('price', 'between', [$start, $end])->where('price','<>', $info['price'])->where('status', '>',
				0)->limit(5)->select();
		$com = new Com();
		$rrr = $com->roomStatus($param['check_in'], $param['check_out'], $info['log'], $info['rooms'],
			$info['num']);
		if(count($rrr)<=0)
		{
			$this->error('该时间没有房间了,换一个类型吧');
		}
		//模板赋值
		$this->assign('res_info', $info);
		$this->assign('rec_you', $rec_you);
		return $this->fetch();
	}
	
	/**
	 * @return mixed
	 * 渲染房型细节
	 */
	public function detail()
	{
		if(!Session::has('user_info')){
			$this->error('你还为登入,正前往登入','login');
		}
		//获取当前房型的id
		$id = $this->request->param('id');
		//根据id信息获取其全部信息
		$cate = new CateModel();
		$room_info = $cate->where('id', $id)->where('status', '>', 0)->find();
		//模板赋值
		$comment = Db::table('comment')->order('id',
			'desc')->where('rec=1')->where('status=1')->limit(5)->select();
		$this->assign('info', $room_info);
		$this->assign('comment', $comment);
		
		//模板输出
		return $this->fetch();
	}
}
