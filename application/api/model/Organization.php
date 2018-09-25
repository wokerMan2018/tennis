<?php
/**
 * fileName: Organization.php
 * User: zhihuanwang
 * Date: 2018/9/19
 * Time: 15:19
 */

namespace app\api\model;
use think\Model;

class Organization extends Model
{
    protected $pk = 'id';
    protected $table = 'zhi_organization';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $auto = [];
    protected $insert = ['create_time', ];
    protected $update = ['update_time'];
}