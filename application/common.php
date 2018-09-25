<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

function responseJSONOld($status, $msg, $res)
{
    header("Content-type:application/json");
    header("HTTP/1.1 $status $msg");
//    echo "hello";
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
//    return json_decode($str);
    exit;
}

function responseJSON($code, $message = '', $data = array())
{
    header('content-type:application/json');
    $result = array(
        'status' => $code,
        'message' => $message,
        'data' => $data
    );
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}