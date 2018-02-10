<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/15
 * Time: 20:04
 */

namespace app\moblie\controller;


use Requests as Req;
use think\Controller;
use think\Session;
use function json_decode;

class Weixin extends Controller
{
	const AppID = 'wxbc4ed92a2630783a';
	const AppSecret = 'ac2933e2a7a6100704228636e51f938a';
	
	public function base()
	{
		$appid = self::AppID;
		$redirect_uri = urlencode('http://120.78.78.42/moblie/weixin/getcode');
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_userinfo&state=liao#wechat_redirect";
		header("location:$url");
	}
	
	public function getcode()
	{
		$appid  = self::AppID;
		$secret = self::AppSecret;
		
		$code = $this->request->param('code');
		$url_100  = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
		$req  = Req::get($url_100);
		$info = $req->body;
		
		$info_obj = json_decode($info);
		$access_token = $info_obj->access_token;
		$openid = $info_obj->openid;
		
		$url_200 = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN ";
		
		$person  = Req::get($url_200);
		
		$person_info = $person->body;
		
		$base = json_decode($person_info);
		
		$baseInfo =array(
			'openid'     =>$base->openid,
			'nickname'   =>$base->nickname,
			'sex'        =>$base->sex,
			'headimgurl' =>$base->headimgurl,
		);
		Session::set('wetchat',$baseInfo);
		$this->redirect('index/userinfo');
	}
	
	public function test()
	{
		$str = '{"access_token":"3_W-GfvviKrJhaqxCggPcYzi2c2m6i-8cZHI4x1-01zuoEdLelLXlu_zhbwVJUo1YUrL7qfE7gE-agPzF4bH73LQ","expires_in":7200,"refresh_token":"3_dzI3SF3DvRZ1oqepUF-P7HYFmuYXViPa5_2ghziMyV4E3XNQkcaaAUGNEVCGdJDQIQk6zqwKhOUlDL9UWU_ypQ","openid":"o5_bo0jIBKVkfOD2GcCn2MFm5DwA","scope":"snsapi_userinfo"}';
		$news = json_decode($str);
		
		echo $news->access_token;
	}
	
	
}
