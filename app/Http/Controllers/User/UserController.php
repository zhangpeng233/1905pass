<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\UserModel;
class UserController extends Controller
{

    public function reg(Request $request)
    {
       echo '<pre>'; print_r($_POST);echo '</pre>'; 
    $pass1 = $request->input('pass1');
    $pass2 = $request->input('pass2');

        //验证密码是否一致
        if($pass1 != $pass2)
        {
            echo "两次输入的密码不一致";
        }
        $name=$request->input('name');
        $email = $request->input('email');
        $mobile = $request->input('mobile');
        //验证 用户表 email mobile 是否已被注册
        $u = UserModel::where(['name'=>$name])->first();
        if($u){
            $response = [
                'error' => 5000001,
                'msg'   => "用户名已被使用"
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        //验证email
        $u = UserModel::where(['email'=>$email])->first();
        if($u){
            $response = [
                'error' => 5000001,
                'msg'   => "email已被禁用"
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

    //验证mobile
        $u = UserModel::where(['mobile'=>$mobile])->first();
        if($u){
            die("电话号已被使用");
        }
        //生成密码
        $password = password_hash($pass1,PASSWORD_BCRYPT);


        //入库
            $user_info = [
                    'email' => $email,
                    'name'  => $name,
                    'mobile' => $mobile,
                    'password' => $password
            ];

       $uid = UserModel::insertGetId($user_info);
       if($uid)
       {
          $response = [
              'error' => 0,
              'msg'   => 'ok'
          ];
       }else{
           $response = [
               'error' => 5000001,
               'msg'   => "服务器内部错误，请稍后再试"
           ];
       }
       die(json_encode($response));

    }
      //用户登录
       public function login(Request $request)
       {

           echo '<pre>';
           print_r($_POST);
           echo '</pre>';
           $value = $request->input('name');
           $pass = $request->input('pass');
           //按name找记录
           $u1 = UserModel::where(['name' => $value])->first();
           $u2 = UserModel::where(['email' => $value])->first();
           $u3 = UserModel::where(['mobile' => $value])->first();
           if ($u1 == NULL && $u2 == NULL && $u3 == NULL) {
               $response = [
                   'errno' => 400004,
                   'msg' => "用户不存在"
               ];
               return $response;
           }
           if ($u1)     // 使用用户名登录
           {
               if (password_verify($pass, $u1->password)) {
                   $uid = $u1->id;
               } else {
                   $response = [
                       'errno' => 400003,
                       'msg' => 'password wrong'
                   ];
                   return $response;
               }
           }
           if ($u2) {        //使用 email 登录
               if (password_verify($pass, $u2->password)) {
                   $uid = $u2->id;
               } else {
                   $response = [
                       'errno' => 400003,
                       'msg' => 'password wrong'
                   ];
                   return $response;
               }
           }
           if ($u3) {        // 使用电话号登录
               if (password_verify($pass, $u3->password)) {
                   $uid = $u3->id;
               } else {
                   $response = [
                       'errno' => 400003,
                       'msg' => 'password wrong'
                   ];
                   return $response;
               }
           }
       }
        protected function getToken($uid)
    {
        $token = md5(time() . mt_rand(11111,99999) . $uid);
        return substr($token,5,20);
    }
        /**
         * 获取用户信息接口
         */
        public function showTime()
    {
        if(empty($_SERVER['HTTP_TOKEN']) || empty($_SERVER['HTTP_UID']))
        {
            $response = [
                'errno' => 40003,
                'msg'   => 'Token Not Valid!'
            ];
            return $response;
        }
        //获取客户端的 token
        $token = $_SERVER['HTTP_TOKEN'];
        $uid = $_SERVER['HTTP_UID'];
        $redis_token_key = 'str:user:token:'.$uid;
        //验证token是否有效
        $cache_token = Redis::get($redis_token_key);
        if($token==$cache_token)        // token 有效
        {
            $data = date("Y-m-d H:i:s");
            $response = [
                'errno' => 0,
                'msg'   => 'ok',
                'data'  => $data
            ];
        }else{
            $response = [
                'errno' => 40003,
                'msg'   => 'Token Not Valid!'
            ];
        }
        return $response;
    }





}
