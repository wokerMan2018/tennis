<?php
/**
 * Created by PhpStorm.
 * User: shaoguo
 * Date: 2018/4/8
 * Time: 下午2:41
 */

namespace app\plugins\jwt;

use \Firebase\JWT\JWT as JWTP;
use think\Model;
use think\facade\Cache;
class JWT extends JWTP
{
    /**
     * @param Model $userModel
     * @param $user 需要验证用户名字段
     * @param $password 需要验证的密码字段
     * @param bool $auto 是否自动登录
     * @param $time 登陆时间
     */
    public function login(Model $userModel,$user='',$password='',$auto=false,$time='720000'){
        if(!$auto){
            $users=$userModel->where($userModel->autoUser,$user)->find();
            if(empty($users)){
                throw new \Exception("用户不存在");
            }
            $autoPass=$userModel->autoPass;
            if(!password_verify($password,$users->{$autoPass})){
                throw new \Exception("用户名或密码错误");
            }
            if(!$users['status'] == 1){
                throw new \Exception("审核未通过");
            }

            $users->last_login_ip=request()->ip();
            $users->last_login_time=date("Y-m-d H:i:s",time());
        }else{
            $users=$userModel;
        }
        $jti=time();
        $api=[
            /**
             *非必须。issued at。 token创建时间，unix时间戳格式
             */
            'lat'=>$_SERVER['REQUEST_TIME'],
            /**
             *非必须。expire 指定token的生命周期。unix时间戳格式
             */
            'exp'=>$_SERVER['REQUEST_TIME']+$time,
            /**
             * 非必须。JWT ID。针对当前token的唯一标识
             */
            'jti'=>$jti,
            /**
             * 自定义字段
             */
            'userModel'=>$users,
        ];
        $token=self::encode($api,config('jwt.key'));
        if(!Cache::set($token,$jti,$time)){
            throw new \Exception("登录失败");
        };

        return $token;
    }

    /**
     * @param Model $userModel用户模型
     * @param $user 用户名
     * @param $password 密码
     * @return string 返回
     * @throws \Exception
     */
    public function register(Model $userModel,$user,$password){
        $login_user=$userModel->autoUser;
        $login_pass=$userModel->autoPass;
        $user=$userModel->create([$login_user=>$user,$login_pass=>$password]);
        if(!$user->id){
            throw new \Exception("注册失败");

        }
        return $this->login($user,'','',true);
    }

    /**
     * 自动登录
     * @param $token
     * @return object
     * @throws \Exception
     */
    public function auth($token=''){

        if(empty($token)){
            $token=request()->header('token');
        }
//        responseJSON(200,'jdfsj',Cache::get($token));
        if(!Cache::get($token)){
            throw new \Exception("token错误或已经过期");
        }
        try{
            $userModel=self::decode($token,config('jwt.key'),config('jwt.type'));

            return $userModel->userModel;
        }catch (\Exception $e){
            throw new \Exception("请重新登录");
        }
    }


    public function logout(){
        $token=request()->request('token');
        Cache::rm($token);
        ajax_success([],'退出登录成功');
    }
}