<?php
use think\Db;

$getComment=$_GET['function'];
//echo $getComment;
$article=$_GET['data'];
$map[]=['status','=',1];
$map[]=['article_id','=',$article];
$res=Db::table('zh_user_comments,zh_user')
    ->where($map)
    ->field('user_name,content,update_time')
    ->order('update_time','desc')
    ->select();

if (isset($article)){
    responseJSON(200,'success',$res);
}else{
    responseJSON(404,'fail',"");
}
function responseJSON($status,$msg,$res)
{
    header("Content-type:application/json");
    header("HTTP/1.1 $status $msg");
    return json_encode($res,JSON_UNESCAPED_UNICODE );
}
