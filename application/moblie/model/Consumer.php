<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/15
 * Time: 12:05
 */

namespace app\moblie\model;


use think\Model;
use think\Session;

class Consumer extends Model
{
	public function login($data)
	{
		//获取用户的信息
		$user = Consumer::getByUsername($data['username']);
		// 动态查询,返回一个用户结果集或者null
		if($user)
		{
			if($user['status']==1)
			{
				if($user['password']==md5($data['password']))
				{
					Session::set('user_info', $user);
					return 4;//正确登入
				} else
				{
					return 3;//密码错误
				}
			} else
			{
				return 2; // 用户已被禁用
			}
		} else
		{
			return 1; // 用户不存在
		}
	}
}
