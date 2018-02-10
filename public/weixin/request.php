<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/8
 * Time: 16:35
 */
$token     = 'demo';
$timestamp = $_GET['timestamp'];
$nonce     = $_GET['nonce'];
$echostr   = $_GET['echostr'];
$signature = $_GET['signature'];
/**
 * @param $token
 * 用户自定义的Token
 * @param $timestamp
 * 微信服务器发送过来的时间戳
 * @param $nonce
 * 随机数
 * @return string
 * 返回经过加密的字符串
 */
function check($token, $timestamp, $nonce)
{
    $array = array($token, $timestamp, $nonce);
    sort($array, SORT_STRING);
    $str = implode($array);
    $sha1 = sha1($str);
    return $sha1;
}
//获取加密后的字符串
$sha1 = check($token, $timestamp, $nonce);

//判断是否相等
if ($signature==$sha1){
    //原样输出这个字符串
    echo $echostr;
} else {
    //写入日志
    $file = fopen('info.txt', 'w');
    fwrite($file, $echostr);
}






