<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/13
 * Time: 17:14
 */

namespace app\api\controller;


use function dump;
use function json_decode;
use function sha1;
use think\Controller;
use \Requests as Req;
use think\Session;
use function time;

class Share extends Controller
{
	public function index()
	{
		$jsapi_ticket = $this->jsapi_ticket();
		
		$appId        = 'wxbc4ed92a2630783a';
		$noncestr     = $this->createNonceStr();
		$serect_url   = 'http://120.78.78.42/api/share/index';
		$timestamp    = time();
		$string       = "jsapi_ticket=$jsapi_ticket&noncestr=$noncestr&timestamp=$timestamp&url=$serect_url";
		$signature    = sha1($string);
		
		$this->assign('appId', $appId);
		$this->assign('noncestr', $noncestr);
		$this->assign('timestamp', $timestamp);
		$this->assign('signature', $signature);
		$this->assign('jsapi_ticket', $jsapi_ticket);
		
		return $this->fetch();
	
	}
	
	private function createNonceStr($length = 16)
	{
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for($i = 0; $i<$length; $i++)
		{
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}
	public function jsapi_ticket()
	{
		$access_token = 'gsbI402dRBOzDhdGXTvaLcL3BKa9xwrGLmOt4GKhu9PNNSoHceHIv2lSD7gRytDZJUyTZBMmQocK9SYPjuRAneiBBaEy2asMKQHba4620tz4GOyfwKxLSwWZeMnNnJhPFUZcAGABQV';
		$url  = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi";
		if(!Session::has('ticket'))
		{
			$req  = Req::get($url);
			$info = json_decode($req->body);
			$jsapi_ticket = $info->ticket;
			Session::set('ticket',$jsapi_ticket);
		}
		return Session::get('ticket');
		
	}
}
