<?php
/**
 * fileName: User.php
 * User: zhihuanwang
 * Date: 2018/9/19
 * Time: 14:28
 */

namespace app\api\controller;

use app\api\model\Organization;
use app\api\model\Referee;
use think\Controller;
use think\Db;
use think\facade\Request;
use app\facade\Jwt;
use app\api\model\User as UserModel;
use think\facade\Session;
use think\facade\Cache;

class User extends Base
{
    /********************************************
     * @purpose  用户请求短信验证
     * @date 2018/9/7 11:11
     * @param
     * @return
     *******************************************/
    public function checkPhone()
    {
        $getData = I('get.');
        // dump($getData);
        // die;
        $param = str_pad(mt_rand(0, 999999), 6, "0", STR_PAD_BOTH);//生成六位随机数

//        $getData=htmlspecialchars_decode($getData);
//        $getData=json_decode($getData,true);
        $phone_num = $getData['phone_number'];

        //发短信部分
        Vendor('alisms.Alisms', '', '.class.php');
        $demo = new SmsDemo(
            "LTAIGC37voorOjMv",
            "C2zkwQ3aP7zOqIvWmpCVJ6UYaZSHHo"
        );
        $response = $demo->sendSms(
            "高顿教育", // 短信签名
            "SMS_113465077", // 短信模板编号
            $phone_num, // 短信接收者
            Array(  // 短信模板中字段的值
                "code" => $param,
                "product" => "dsd"
            ),
            "123"
        );
        //发短信部分结束
//        写入数据表
        Db::table('zhi_phone_check')->data([
            'phone_number' => $phone_num,
            'phone_code' => $param,
            'create_time' => date('Y-m-d H:i:s', time())
        ])->insert();

    }

    /********************************************
     * @purpose  查询用户信息
     * @date 2018/9/20 18:00
     * @param
     * @return
     *******************************************/
    public function getUserInfo()
    {
        $this->auth(false);
        $data = $this->user;
        responseJSON(200, '获取用户成功', $data);
    }

    /********************************************
     * @purpose  查询裁判员信息
     * @date 2018/9/21 15:19
     * @param
     * @return
     *******************************************/
    public function getRefereeInfo()
    {
        $this->auth(false);
        $user = $this->user;
        $referee = Referee::where(['uid' => $user->id])->find();
        responseJSON(200, '返回裁判员信息', $referee);
    }

    /********************************************
     * @purpose  查询机构信息
     * @date 2018/9/21 16:26
     * @param
     * @return
     *******************************************/
    public function getOrganizationInfo()
    {
        $this->auth(false);
        $user = $this->user;
        $organization = Organization::where(['uid' => $user->id])->find();
        responseJSON(200, '返回机构信息', $organization);
    }

    /********************************************
     * @purpose  普通用户注册
     * @date 2018/9/19 14:30
     * @param
     * @return
     *******************************************/
    public function doRegister()
    {
        if (Request::isPost()) {
            $data = Request::post();

            $rule = 'app\api\validate\User';

            $res = $this->validate($data, $rule);
//            dump($res);
//            die;
            if (true !== $res) {
                responseJSON(400, $res);
            } else {
                $code = Db::table('zhi_phone_check')->where(['phone_number' => $data['phone_number']])->value('phone_code');

                $data['usergroupid'] = 1;
                if (strlen($data['document_number']) == 18) {
                    $sex = substr($data['document_number'], 16, 1);
                    $data['gender'] = $sex % 2 == 1 ? '男' : '女';
                }

                if ($code == $data['phone_code']) {
                    unset($data['phone_code']);
                    $user = UserModel::create($data);
                    if (!$user->id) {
                        responseJSON(402, '注册失败');
                    } else {
                        $token = Jwt::login($user, '', '', $auto = true);
                        $user->token = $token;
                        $user->save();
                        responseJSON(200, '注册成功', $token);
                    }
                } else {
                    responseJSON(401, '验证码错误');
                }
            }
        } else {
            responseJSON(403, '请求类型错误');
        }
    }

    /********************************************
     * @purpose  普通用户登录
     * @date 2018/9/19 14:31
     * @param
     * @return
     *******************************************/
    public function doLogin()
    {
        if (Request::isPost()) {
            $postData = Request::post();
            $rule = [
                'phone_number|手机号' => 'require|mobile',
                'password|密码' => 'require'
            ];
            $res = $this->validate($postData, $rule);
            if (true !== $res) {
                responseJSON(400, $res);//初步过滤数据,验证数据合法性
            } else {
                $user = UserModel::get(function ($query) use ($postData) {
                    $query->where(['phone_number' => $postData['phone_number']]);
                });
                if (empty($user)) {
                    responseJSON(401, '没有该手机号');
                }
                if (!password_verify($postData['password'], $user->password)) {
                    responseJSON(402, '密码错误');
                }
                $jti = time();
                $time = '720000';
                $api = [
                    /**
                     *非必须。issued at。 token创建时间，unix时间戳格式
                     */
                    'lat' => $_SERVER['REQUEST_TIME'],
                    /**
                     *非必须。expire 指定token的生命周期。unix时间戳格式
                     */
                    'exp' => $_SERVER['REQUEST_TIME'] + $time,
                    /**
                     * 非必须。JWT ID。针对当前token的唯一标识
                     */
                    'jti' => $jti,
                    /**
                     * 自定义字段
                     */
                    'userModel' => $user,
                ];
                $token = JWT::encode($api, config('jwt.key'));
                $user->token = $token;
                $user->save();
                $response['user'] = $user;
                $response['token'] = $token;
                if (!Cache::set($token, $jti, $time)) {
                    throw new \Exception("登录失败");
                };
                responseJSON(200, '登录成功', $response);
            }
        }
    }

    /********************************************
     * @purpose  普通用户登出
     * @date 2018/9/19 14:31
     * @param
     * @return
     *******************************************/
    public function doLogout()
    {
        if (Request::isPost()) {
            $postData = Request::post();
            $token = Request::header('token');

            $user = UserModel::get(function ($query) use ($postData) {
                $query->where(['phone_number' => $postData['phone_number']]);
            });
            $user->token = '';
            $user->save();
            Cache::rm($token);

            responseJSON(200, '退出登录成功');
        } else {
            responseJSON(400, '请求类型错误');
        }
    }


    /********************************************
     * @purpose  普通用户到裁判申请
     * @date 2018/9/19 14:32
     * @param
     * @return
     *******************************************/
    public function doUserToReferee()
    {
        $this->auth(false);
        $user = $this->user;
        $userInfo = UserModel::where(['id' => $user->id])->field('id,name,gender,document_type,document_number,phone_number,password')->find();
        if (Request::isPost()) {
            $postData = Request::post();
            $rule = [
                'referee_description|个人简介' => 'require',
                'referee_certificate|资质证明' => 'require'
            ];
            $res = $this->validate($postData, $rule);
//            if (true !== $res) {
//                responseJSON(400, $res);
//            }
            $img = Request::file('referee_certificate');
            $info = $img->move('./uploads/cert');
            if ($info) {
                $postData['referee_certificate'] = '/public/uploads/cert/' . str_replace('\\', '/', $info->getSaveName());
            } else {
                responseJSON(400, $info->getError());
            }
            if (Referee::where(['uid' => $userInfo->id])->find()) {
                $bool = Referee::where(['uid' => $userInfo->id])->update([
                    'referee_description' => $postData['referee_description'],
                    'referee_certificate' => $postData['referee_certificate']
                ]);
            } else {
                $bool = Referee::create([
                    'uid' => $userInfo->id,
                    'usergroupid' => 2,
                    'status' => 0,
                    'name' => $userInfo->name,
                    'gender' => $userInfo->gender,
                    'document_type' => $userInfo->document_type,
                    'document_number' => $userInfo->document_number,
                    'phone_number' => $userInfo->phone_number,
                    'referee_description' => $postData['referee_description'],
                    'referee_certificate' => $postData['referee_certificate']
                ]);
            }
            if ($bool) {
                responseJSON(200, '提交成功', ['dataInfo' => '您的信息已经提交成功，请留意手机短息，审核结果会通过短息告知。']);
            }
        }

    }

    public function test()
    {
        $user = UserModel::where(['id' => 33])->field('id,name,gender,document_type,document_number,phone_number,password')->find();
        responseJSON(200, 'hdjh', $user);

    }

    /********************************************
     * @purpose  普通用户到机构申请
     * @date 2018/9/19 15:15
     * @param
     * @return
     *******************************************/
    public function doUserToOrganization()
    {
        $this->auth(false);
        $user = $this->user;
        if (Request::isPost()) {
            $postData = Request::post();
            $rule = [
                'name|机构名称' => 'require',
                'head_name|负责人姓名' => 'require',
                'head_phone|负责人联系方式' => 'require',
                'logo|机构logo' => 'require',
                'description|机构简介' => 'require',
                'organization_certificate|机构资质证书' => 'require'
            ];
            $res = $this->validate($postData, $rule);
//            if (true !== $res) {
//                responseJSON(400, $res);
//            }
            $logo = Request::file('logo');
            $logo = $logo->move('./uploads/cert');
            $organization_certificate = Request::file('organization_certificate');
            $organization_certificate = $organization_certificate->move('./uploads/cert');
            $message = array('organization_certificateError' => '', 'logoError' => '');
            if ($logo) {
                $postData['logo'] = '/public/uploads/cert/' . str_replace('\\', '/', $logo->getSaveName());
            } else {
                $message['logoError'] = $logo->getError();
            }
            if ($organization_certificate) {
                $postData['organization_certificate'] = '/public/uploads/cert/' . str_replace('\\', '/', $organization_certificate->getSaveName());
            } else {
                $message['organization_certificateError'] = $organization_certificate->getError();
            }
            if (!$message['logoError'] AND !$message['organization_certificateError']) {
                if (Organization::where(['uid' => $user->id])->find()) {
                    $orga = Organization::where(['uid' => $userInfo->id])->update([
                        'name' => $postData['name'],
                        'head_name' => $postData['head_name'],
                        'head_phone' => $postData['head_phone'],
                        'logo' => $postData['logo'],
                        'description' => $postData['description'],
                        'organization_certificate' => $postData['organization_certificate']
                    ]);
                } else {
                    $orga = Organization::create([
                        'usergroupid' => 3,
                        'name' => $postData['name'],
                        'uid' => $user->id,
                        'status' => 0,
                        'head_name' => $postData['head_name'],
                        'head_phone' => $postData['head_phone'],
                        'logo' => $postData['logo'],
                        'description' => $postData['description'],
                        'organization_certificate' => $postData['organization_certificate']
                    ]);
                }
                if ($orga) {
                    responseJSON(200, '提交成功', ['dataInfo' => '您的信息已经提交成功，请留意手机短息，审核结果会通过短息告知。']);
                }

            } else {
                responseJSON(400, '图片上传错误', $message);
            }

        }


    }

    /********************************************
     * @purpose
     * @date 2018/9/25 14:39
     * @param
     * @return
     *******************************************/


    public function functionNameHello()
    {

    }
}
        
