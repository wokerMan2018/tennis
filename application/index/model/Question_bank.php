<?php
/**
 * fileName: Question_bank.php
 * User: zhihuanwang
 * Date: 2018/9/17
 * Time: 16:44
 */
namespace app\index\model;

use think\Model;

class Question_bank extends Model
{
    protected $pk = 'id';
    protected $table= 'zhi_question_bank';
    protected $autoWriteTimestamp='datetime';
    protected $createTime='create_time';
    protected $updateTime='update_time';

    protected $auto = [];
    protected $insert = ['create_time', 'question_type'=>1];
    protected $update = ['update_time'];


}