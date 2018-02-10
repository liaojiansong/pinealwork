<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        $info      = $this->request->param();
        $token     = 'demo';
        $timestamp = $info['timestamp'];
        $nonce     = $info['nonce'];
        $echostr   = $info['echostr'];
        $signature = $info['signature'];
        $sha1 = $this->get_sha1($token, $timestamp, $nonce);
        if ($signature == $sha1) {
            //原样输出这个字符串
            echo $echostr;
        } else {
            //写入日志
            $file = fopen('info.txt', 'w');
            fwrite($file, $echostr);
        }

    }

    private function get_sha1($token, $timestamp, $nonce)
    {
        $array = array($token, $timestamp, $nonce);
        sort($array, SORT_STRING);
        $str = implode($array);
        $sha1 = sha1($str);
        return $sha1;
    }
}
