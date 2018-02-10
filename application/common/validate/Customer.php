<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/21
 * Time: 14:27
 */

namespace app\common\validate;
use think\Validate;

/**
 * Class Customer
 * 验证器
 * @package app\common\validate
 */
class Customer extends Validate
{
    protected $regex =['num'=>'(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)'];
    protected $rule = [
        //入住提交信息的时候
        'check_in'            => 'date',
        'check_out'           => 'date',
        'tmp_room'            =>'require',
        'name'                =>'require|min:2|max:32',
        'phone'               =>'require|/^1[34578]\d{9}$/',
//        'id_card'             => 'require|/^[1-9]\d{17}$/',
//        'id_card'             =>'require|regex:num|token',
//    2018/1/18修改
        'id_card' => 'require|regex:num',
        //管理员登入的时候
        'username'            =>'require|min:3|max:16',
        'password'            =>'require|min:6|max:16',
        //上传房间的时候
        'type'                =>'require|min:4|max:64',
        'price'               =>'require|number',
        'rooms'               =>'require',
        'description'         =>'require|min:18',
        'thumb'               =>'require',
        'thumb_bed'           =>'require',
        'thumb_bathroom'      =>'require',
        //用户留言
        'customer'            =>'require',
        'title'               =>'require',
        'email'               =>'require|email',
        'content'             =>'require|min:2',
	    
	    //用户注册
     


    ];

    protected $message = [
        'check_in'        => '日期不合法',
        'check_out'       => '日期不合法',

        'name.require'    =>'请输入用户名',
        'name.min'        =>'客户名需大于2位',
        'name.max'        =>'客户名需小于32位',

        'phone.require'       =>'请输入手机号码',
        'phone'               =>'请输入正确的手机号码',

        'id_card.require'     => '请输入身份证号码',
        'id_card.regex'       =>'请输入正确的身份证号码',

        'username.require'    =>'请输入用户名',
        'username.min'        =>'请输入3-16位用户名',
        'username.max'        =>'请输入3-16位用户名',


        'password.require'    =>'请输入密码',
        'password.min'        =>'请输入6-16位的密码',
        'password.max'        =>'请输入6-16位的密码',

        'type.require' => '请输入房型名',
        'type.min' => '房型名需大于4位',
        'type.max' => '房型名需小于32位',

        'price.require'       =>'请输入房间价格',
        'price.number'        =>'房间价格必须为正整数',

        'description.require' =>'请输入对该房型的描述',
        'description.min'     =>'对该房型的描述需大于16字',

        'thumb.require'       =>'请上传主卧鱼片',
        'thumb_bed.require'   =>'请上传床的大图',
        'thumb_bathroom.require'=>'请上传浴室的图片',

        'customer.require'      =>'请输入您的名字',
        'title.require'         =>'请输入留言的主题',
        'email.require'         =>'请输入您的邮箱',
        'email.email'           =>'请输入合法的邮箱地址',
        'content.require'       =>'请输入你的留言内容',
        'content.min'           =>'留下几个字吧',




    ];

    protected $scene=[
        'ordering'=>['check_in', 'check_out','phone','id_card','name'],
        'login'   =>['username','password'],
        'upload'  =>['type','price','description'],
        'comment' =>['customer','title','content','email'],
	    'reg'     =>['username','password','phone','email'],
        'test'  =>['username'],
//        'upload'  =>['type','price','description','thumb','thumb_bed','thumb_bathroom'],
//        'upload'  =>['type','price','description','thumb','thumb_bed','thumb_bathroom'],
    ];
}
