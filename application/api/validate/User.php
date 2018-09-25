<?php
/**
 * fileName: User.php
 * User: zhihuanwang
 * Date: 2018/9/19
 * Time: 16:05
 */

namespace app\api\validate;
use think\Validate;

class User extends Validate
{
    protected $rule =[
        'name|姓名'=>'require',
        'document_type|证件类型'=>'require',
        'document_number|证件号码'=>'require',
        'phone_number|手机号'=>'require|mobile|unique:zhi_user',
        'password|密码'=>'require',
        'phone_code|验证码'=>'require'
    ];
}