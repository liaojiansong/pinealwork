<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/19
 * Time: 18:21
 */

namespace app\api\controller;


use Redis;

class RedisHandler extends Redis
{
	
	protected static $redis = null;
	
	public static function getInstance()
	{
		if(null===self::$redis)
		{
			self::$redis = new Redis();
			//$host = $_SERVER['REDIS_HOST'];
			$host = '127.0.0.1';
			//$port = $_SERVER['REDIS_PORT'];
			$port = 6379;
			self::$redis->connect($host, $port);
		}
		return self::$redis;
	}
	
}

