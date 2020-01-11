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

        echo '<pre>'; print_r($_POST);echo '</pre>';
        $value = $request->input('name');
        $pass = $request->input('pass');
        //按name找记录
        $u = UserModel::where(['name'=>$value])->first();

        echo '<pre>'; print_r($u->toArray());echo '</pre>';
            //验证密码
        if(password_verify($pass,$u->password)){



        }else{
            $response =[
                'error' => 5000004,
                'msg'   =>'密码错误'
            ];
            return $response;

        } echo '<pre>'; print_r($u->toArray());echo '</pre>';


    }








}
