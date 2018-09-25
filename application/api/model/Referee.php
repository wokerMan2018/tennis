<?php
/**
 * fileName: Referee.php
 * User: zhihuanwang
 * Date: 2018/9/19
 * Time: 15:18
 */

namespace app\api\model;
use think\Model;

class Referee extends Model
{
    protected $pk = 'id';
    protected $table = 'zhi_referee';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $auto = [];
    protected $insert = ['create_time'];
    protected $update = ['update_time'];
}