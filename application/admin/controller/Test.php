<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/21
 * Time: 10:19
 */

namespace app\admin\controller;


use function dump;
use think\Controller;
use think\Db;
use think\Loader;
use think\Session;

class Test extends Controller
{
    public function test()
    {
        $data = ['phone'=>'','id_card'=>'450924199511124975'];
        $res  =$this->validate($data,'Customer.test');
        dump($res);//返回错误信息或者true


    }
}
