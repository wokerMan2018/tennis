<?php
/**
 * fileName: Base.php
 * User: zhihuanwang
 * Date: 2018/9/20
 * Time: 20:04
 */

namespace app\api\controller;


use think\Controller;
use app\facade\Jwt;

class Base extends Controller
{
    protected $user;
    /**
     * 判断是否登陆
     */
    public function auth($flag=true)
    {
        try{
            $this->user=Jwt::auth();

        }catch (\Exception $e){
            if($flag){
                ajax_error([],$e->getMessage());
            }

        }
    }

    public function method($method='get'){
        $is_method="is".ucfirst($method);
        if(!request()->$is_method()){
            ajax_error('','请求方式此错误');
        }
    }
}