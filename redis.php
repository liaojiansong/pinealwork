<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/13
 * Time: 14:13
 */
$redis = new Redis();
$redis->connect('127.0.0.1');
$redis->set('name', 'liaojiansong');
