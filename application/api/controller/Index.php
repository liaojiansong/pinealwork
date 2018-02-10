<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/11
 * Time: 17:39
 */

namespace app\api\controller;

use function dump;
use Redis;
use think\Controller;
use think\Session;
use function file_get_contents;
use function fopen;
use function header;
use function json_decode;
use function ob_clean;
use function time;
use function urlencode;
use function var_dump;
use \Requests as Req;
use app\api\controller\RedisHandler as Red;
class Index extends Controller
{
	const AppID     = 'wxbc4ed92a2630783a';
	const AppSecret = 'ac2933e2a7a6100704228636e51f938a';
	public function index()
	{
		ob_clean();
		$this->log();
		$this->responseMsg();
	}
	
	/**
	 * 获取临时二维码
	 */
	public function getQRcode()
	{
		$access_token = $this->get_access_token();
		//post请求的url
		$url  = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$access_token";
		$data = '{
				  "expire_seconds": 604800,
				  "action_name": "QR_STR_SCENE",
				  "action_info": {
				    "scene": {
				      "scene_str": "欢迎扫我"
				    }
				  }
				}';
		//采用post请求
		$req = Req::post($url,array(),$data);
		//返回的是json数据,包含ticket和url什么鬼的
		$info = $req->body;
		//解析为对象
		$info_obj       = json_decode($info);
		$ticket         = $info_obj->ticket;
		//$expire_seconds = $info_obj->expire_seconds;
		//$return_url     = $info_obj->url;
		//编码ticket,官方要求
		$en_ticket      = urlencode($ticket);
		//拼装url
		$qr_code        = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=$en_ticket";
		//直接跳转,是张图片
		$this->redirect($qr_code);

	}
	
	/**
	 * 网页授权回调地址
	 */
	public function baseinfo()
	{
		$appid = self::AppID;
		$redirect_uri = urlencode('http://120.78.78.42/api/index/getcode');
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_userinfo&state=liao#wechat_redirect";
		header("location:$url");
	}
	
	/**
	 * 通过get获取code并保存
	 */
	public function getcode()
	{
		$appid = self::AppID;
		$secret = self::AppSecret;
		$code   = $this->request->param('code');
		var_dump($code);
		Session::set('code',$code);
		
	}
	
	/**
	 * 获取用户基本信息
	 */
	public function getinfo()
	{
		$appid  = self::AppID;
		$secret = self::AppSecret;
		$code   =Session::get('code');
		if(!Session::has('person_info'))
		{
			//拼装请求access_token的url
			$url_1 = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
		
			//获取access_token,返回的是json数据
			$info = file_get_contents($url_1);
			Session::set('person_info',$info);
		}
		
		
		$info = Session::get('person_info');
		//解析json数据为对象
		$acce = json_decode($info);
		//
		$access_token =$acce->access_token;
		$openid       =$acce->openid;
		////拼装获取用户信息的url
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
		//返回json数据
		$person = file_get_contents($url);
		var_dump($person);
		//解析json数据为对象
		$last = json_decode($person);
		
		echo $last->openid;
		echo '<br>';
		echo $last->nickname;
		echo '<br>';
		echo $last->city;
		echo '<br>';
		echo $last->headimgurl;
		
	}
	
	/**
	 * 打印微信服务器发送过来的所有信息
	 */
	public function log()
	{
		$postStr = file_get_contents("php://input");
		$file = fopen(__DIR__ . '_Info.txt', 'w+');
		fwrite($file, $postStr);
		fclose($file);
	}
	
	/**
	 * 响应的方法
	 */
	public function responseMsg()
	{
		//get post data, May be due to the different environments
		$postStr = file_get_contents("php://input");
		
		//extract post data
		if(!empty($postStr))
		{
			/* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
			   the best way is to check the validity of xml by yourself */
			libxml_disable_entity_loader(true);
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$msgType = $postObj->MsgType;
			$EventKey= $postObj->EventKey;
			$keyword = trim($postObj->Content);
			$time = time();
			
			$textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[text]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
			$musicTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[music]]></MsgType>
							<Music>
							<Title><![CDATA[%s]]></Title>
							<Description><![CDATA[%s]]></Description>
							<MusicUrl><![CDATA[%s]]></MusicUrl>
							<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
						
							</Music>
							</xml>";
			$newsTpl = "<xml>
								<ToUserName><![CDATA[%s]]></ToUserName>
								<FromUserName><![CDATA[%s]]></FromUserName>
								<CreateTime>%s</CreateTime>
								<MsgType><![CDATA[news]]></MsgType>
								<ArticleCount>%s</ArticleCount>
								<Articles>
									<item>
									<Title><![CDATA[%s]]></Title>
									<Description><![CDATA[%s]]></Description>
									<PicUrl><![CDATA[%s]]></PicUrl>
									<Url><![CDATA[%s]]></Url>
									</item>
									<item>
									<Title><![CDATA[%s]]></Title>
									<Description><![CDATA[%s]]></Description>
									<PicUrl><![CDATA[%s]]></PicUrl>
									<Url><![CDATA[%s]]></Url>
									</item>
									<item>
									<Title><![CDATA[%s]]></Title>
									<Description><![CDATA[%s]]></Description>
									<PicUrl><![CDATA[%s]]></PicUrl>
									<Url><![CDATA[%s]]></Url>
									</item>
								</Articles>
							</xml>";
			//if(!empty($keyword))
			//{
			if($keyword=='号码')
			{
				$content = "[1]精神病医院\n[2]银行号码";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $content);
				echo $resultStr;
			} elseif($keyword==1)
			{
				$content = "青山精神病医院号码为122210";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $content);
				echo $resultStr;
			} elseif($keyword==2)
			{
				$content = "农业银行666445455";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $content);
				echo $resultStr;
			} elseif($keyword=='音乐')
			{
				$title = "世界第一等";
				$url = 'http://120.78.78.42/music.mp3';
				$hq_url = 'http://120.78.78.42/music.mp3';
				$Description = '伍佰给刘德华写的歌';
				$resultStr = sprintf($musicTpl, $fromUsername, $toUsername, $time,  $title,
					$Description, $url, $hq_url);
				echo $resultStr;
			} elseif($keyword=='新闻' || $EventKey == 'news')
			{
				$count = 3;
				$title1 = "世界第一等";
				$desc1 = '伍佰大佬写歌给刘德华';
				$pic_url1 = 'http://120.78.78.42/1.jpg';
				$tit_url1 = 'http://www.glut.edu.cn/';
				
				$title2 = "想看吗?也不看看自己是什么蛤蟆";
				$desc2 = '伍佰大佬写歌给刘德华';
				$pic_url2 = 'http://120.78.78.42/2.jpg';
				$tit_url2 = 'http://www.glut.edu.cn/';
				
				$title3 = "震惊!桂林理工大学竟然发生";
				$desc3 = '伍佰大佬写歌给刘德华';
				$pic_url3 = 'http://120.78.78.42/3.jpg';
				$tit_url3 = 'http://www.glut.edu.cn/';
				
				$resultStr = sprintf($newsTpl, $fromUsername, $toUsername, $time, $count, $title1,
					$desc1, $pic_url1, $tit_url1, $title2, $desc2, $pic_url2, $tit_url2, $title3, $desc3,
					$pic_url3, $tit_url3);
				echo $resultStr;
			} elseif($msgType=='location')
			{
				$Location_X = $postObj->Location_X;
				$Location_Y = $postObj->Location_Y;
				$Label = $postObj->Label;
				$content = "纬度为:$Location_X\n经度为:$Location_Y\n具体位置为:$Label";
				
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $content);
				echo $resultStr;
			} elseif($EventKey == '欢迎扫我')
			{
				$content = "讨厌,为什么要关注人家";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $content);
				echo $resultStr;
			} else
			{
				$data = array(
					'key'    => '1f9fdba5d62a409c89f5be081e1dbb2c',
					'info'   => $keyword,
					'userid' => 'liaojiansong'
				);
				$url = "http://www.tuling123.com/openapi/api";
				$res = $this->http_post_data($url, $data);
				
				$content = json_decode($res[1])->text;
				
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $content);
				echo $resultStr;
				
				
			}
			
			
		}

	}
	
	public function http_get_data($url)
	{
		//1.初始化curl句柄
		$ch = curl_init();
		//2.设置curl
		//设置访问url
		curl_setopt($ch, CURLOPT_URL, $url);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		// 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
		// 1如果成功只将结果返回，不自动输出任何内容。如果失败返回FALSE
		// 运行cURL，请求网页
		$data = curl_exec($ch);
		// 关闭URL请求
		curl_close($ch);
		
		return $data;
	}
	protected function http_post_data($url, $data)
	{
		
		//将数组转成json格式
		$data = json_encode($data);
		
		//1.初始化curl句柄
		$ch = curl_init();
		
		//2.设置curl
		//设置访问url
		curl_setopt($ch, CURLOPT_URL, $url);
		
		//捕获内容，但不输出
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		//模拟发送POST请求
		curl_setopt($ch, CURLOPT_POST, 1);
		
		//发送POST请求时传递的参数
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		
		//设置头信息
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json; charset=utf-8',
			'Content-Length: ' . strlen($data)
		));
		
		//3.执行curl_exec($ch)
		$return_content = curl_exec($ch);
		
		//如果获取失败返回错误信息
		if($return_content===false)
		{
			$return_content = curl_errno($ch);
		}
		
		$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		//4.关闭curl
		curl_close($ch);
		
		return array($return_code, $return_content);
	}
	
	/**
	 * 验证token
	 */
	private function check_api()
	{
		$param = $this->request->param();
		$echoStr = $param["echostr"];
		if($this->checkSignature())
		{
			ob_clean();
			echo $echoStr;
			exit;
		}
	}
	
	/**
	 * @return bool
	 * 检验是否来自微信的请求
	 */
	private function checkSignature()
	{
		$param = $this->request->param();
		
		
		$signature = $param["signature"];
		$timestamp = $param["timestamp"];
		$nonce     =    $param["nonce"];
		
		$token = 'demo';
		$tmpArr = array($token, $timestamp, $nonce);
		// use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		
		if($tmpStr==$signature)
		{
			return true;
		} else
		{
			return false;
		}
	}
	
	/**
	 * @return bool|string
	 * 获取access_token
	 */
	public function get_access_token()
	{
		//实例化redis
		$redis = Red::getInstance();
		//获取access_token过期时间
		$time_left = $redis->ttl('access_token');
		//如果过期.重新获取,否则直接返回
		if($time_left<=0)
		{
			$appid         = self::AppID;
			$secret        = self::AppSecret;
			$url_access_get="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
			$request       = Req::get($url_access_get);
			$access_info   = $request->body;
			$access_obj    = json_decode($access_info);
			$access_token  = $access_obj->access_token;
			$redis->set('access_token', $access_token,7200);
			return $access_token;
		} else
		{
			$access_token  = $redis->get('access_token');
			return $access_token;
		}
	}
	
	
	
}
