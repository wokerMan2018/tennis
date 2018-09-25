<?php
/**
 * fileName: User.php
 * User: zhihuanwang
 * Date: 2018/9/19
 * Time: 15:17
 */

namespace app\api\model;

use think\Model;

class User extends Model
{
    protected $pk = 'id';
    protected $table = 'zhi_user';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $auto = [];
    protected $insert = ['create_time',];
    protected $update = ['update_time'];
    public $autoUser = 'phone_number';
    public $autoPass = 'password';

    public function setPasswordAttr($value)
    {
        return password_hash($value, PASSWORD_BCRYPT);
    }
}