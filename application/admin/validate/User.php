<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/30
 * Time: 14:28
 */
namespace app\admin\validate;

use think\Validate;

/**
 *
 * @author Administrator
 *         验证信息,主要验证用户的输入
 *        
 */
class User extends Validate
{

    protected $rule = [
        'username' => 'require|max:6',
        'password' => 'require|min:3|max:8'
    ];

    protected $message = [
        'username.require' => '请输入用户名',
        'username.max' => '用户名最大为6位',
        'password.require' => '请输入密码',
        'password.min' => '密码小于三位',
        'password.max' => '密码大于了八位'
    ];

    protected $scene = [
        'add' => [
            'username',
            'password'
        ],
        'edit' => [
            'password'
        ]
    ];
}