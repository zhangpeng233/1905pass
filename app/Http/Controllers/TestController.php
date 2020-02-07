<?php
namespace App\Http\Controllers;

use App\User\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class TestController extends Controller
{
    //
    function reg(Request $request)
    {

        $password = $request->input('password');
//        echo $password;die;
        $pass2 = $request->input('pass2');
        if ($password != $pass2) {
            $response = [
                'errno' => '40000',
                'msg' => '两次密码输入不一致'
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

//        print_r($request->input());die;
        $username = $request->input('username');
        $mobile = $request->input('mobile');
        $email = $request->input('email');

        $u = UserModel::where(['username' => $username])->first();
        if ($u) {
            $response = [
                'errno' => 500002,
                'msg' => "用户名已被使用"
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
        $u = UserModel::where(['mobile' => $mobile])->first();
        if ($u) {
            $response = [
                'errno' => 500003,
                'msg' => "手机号已被使用"
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }
        $u = UserModel::where(['email' => $email])->first();
        if ($u) {
            $response = [
                'errno' => 500004,
                'msg' => "邮箱已被使用"
            ];
            die(json_encode($response, JSON_UNESCAPED_UNICODE));
        }

        $password = password_hash($password, PASSWORD_BCRYPT);
        $data = [
            'username' => $username,
            'password' => $password,
            'mobile' => $mobile,
            'email' => $email,
        ];

        $uid = UserModel::insertGetId($data);
        if ($uid) {
            $res = [
                'errno'=>0,
                'msg' => '注册成功'
            ];
        } else {
            $res = [
                'errno'=>40003,
                'msg' => '注册失败'
            ];
        }
        die(json_encode($res,JSON_UNESCAPED_UNICODE));
    }


    function login(Request $request){
        $name=$request->input('username');
        $pass=$request->input('password');
//        echo $pass;
        $user=UserModel::where(['username'=>$name])->first();

        if(empty($user)){
            $response=[
                'errno'=>'40000',
                'msg'=>"用户名不存在"
            ];
            return $response;
        }
        if($user){
            if(password_verify($pass,$user->password)){
                $uid=$user->id;
            }else{
                $response=[
                    'errno'=>'40003',
                    'msg'=>'password wrong'
                ];
                return $response;
            }
        }

        //生成token
        $token = md5(time() . mt_rand(11111,99999) . $uid);
        $key='1905passport:'.$uid;
        Redis::set($key,$token,84600);
        $response=[
            'errno'=>0,
            'msg'=>'ok',
            'data'=>[
                'uid'=>$uid,
                'token'=>$token
            ]
        ];
        return $response;


    }

    function info(){
//        print_r($_SERVER);
//        echo 11;die;

        if(empty($_SERVER['HTTP_TOKEN'])||empty($_SERVER['HTTP_UID'])){
            $response=[
                'errno'=>40003,
                'msg'=>'token 过期',
            ];
            return $response;

        }

        //获取客户端的token
        $token=$_SERVER['HTTP_TOKEN'];
        $uid=$_SERVER['HTTP_UID'];

        $key='1905passport:'.$uid;
        $cache_token=Redis::get($key);
        if($token==$cache_token){
            $data=date('Y-M-d H:i:s');
            $response=[
                'errno'=>0,
                'msg'=>'ok',
                'data'=>$data
            ];
        }else{
            $response=[
                'errno'=>40003,
                'msg'=>'token 过期',
            ];
        }
        return $response;
    }

    public function auth(){
        $uid=$_POST['uid'];
        $token=$_POST['token'];

        if(empty($_SERVER['HTTP_TOKEN'])||empty($_SERVER['HTTP_UID'])){
            $response=[
                'errno'=>40003,
                'msg'=>'token 过期',
            ];
            return $response;
        }
//        //获取客户端的token
//        $token=$_SERVER['HTTP_TOKEN'];
//        $uid=$_SERVER['HTTP_UID'];
        $key='1905passport:'.$uid;
        $cache_token=Redis::get($key);
        if($token==$cache_token){
            $data=date('Y-M-d H:i:s');
            $response=[
                'errno'=>0,
                'msg'=>'ok',
                'data'=>$data
            ];
        }else{
            $response=[
                'errno'=>40003,
                'msg'=>'token 过期',
            ];
        }
        return $response;
    }

    //签名
   
}