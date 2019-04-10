<?php

namespace App\Http\Controllers\Test;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\UserModel;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{
    //移动端登录
    public function appLogin(){
        $u_name=$_POST['u_name'];
        $u_pwd=$_POST['u_pwd'];
        if(empty($u_name)){
            $res_data=[
                'errcode'=>'50001',
                'msg'=>'账号不能为空'
            ];
            return $res_data;
        }
        if(empty($u_pwd)){
            $res_data=[
                'errcode'=>'50002',
                'msg'=>'密码不能为空'
            ];
            return $res_data;
        }
        $user_where=[
            'u_name'  =>  $u_name
        ];
        $user_data=UserModel::where($user_where)->first();
        if($user_data){
            $token = substr(md5(time().mt_rand(1,99999)),10,10);
            setcookie('uid',$user_data->uid,time()+86400,'/','xuesong.shansister.com',false,true);
            setcookie('token',$token,time()+86400,'/','',false,true);
            $redis_key_app_token='app:str:u:token:'.$user_data->uid;
            Redis::del($redis_key_app_token);
            Redis::set($redis_key_app_token,$token);
            Redis::expire($redis_key_app_token,3600*24);
            $res_data=[
                'errcode'=>0,
                'msg'=>'登陆成功',
                'token'=>$token,
                'uid'=>$user_data['uid'],
                'u_name'=>$user_data['u_name'],
            ];
        }else{
            $res_data=[
                'errcode'=>'50003',
                'msg'=>'账号或者密码错误'
            ];
        }
        return $res_data;
    }

    public function center2(){
        $uid=$_POST['uid'];
        $token=$_POST['token'];
        $ktoken='token:u:'.$uid;
        $redis_token=Redis::get($ktoken,'app:token');
        if($token==$redis_token){
            $user_info=UserModel::where(['uid'=>$uid])->first();
            $data=[
                'errcode'=>0,
                'msg'=>'ok',
                'u_name'=>$user_info['u_name'],
            ];
        }else{
            $data=[
                'errcode'=>5001,
                'msg'=>'no'
            ];
        }
        return $data;
    }

    //PC端登录页面
    public function login(){
        return view('user.login');
    }

    public function doLogin(Request $request){
        $u_name=$request->input('u_name');
        $u_pwd=$request->input('u_pwd');
        if(empty($u_name)){
            echo "账号不能为空";
        }
        if(empty($u_pwd)){
            echo "密码不能为空";
        }
        $where=[
            'u_name' => $u_name
        ];
        $userInfo=UserModel::where($where)->first();
        if($userInfo){
            $token = substr(md5(time().mt_rand(1,99999)),10,10);
            setcookie('uid',$userInfo->uid,time()+86400,'/','xuesong.shansister.com',false,true);
            setcookie('token',$token,time()+86400,'/','',false,true);
            $redis_key_web_token='web:str:u:token:'.$userInfo->uid;
            Redis::del($redis_key_web_token);
            Redis::set($redis_key_web_token,$token);
            Redis::expire($redis_key_web_token,3600*24);
            UserModel::where($where)->update(['is_online' => 1]);
            echo "登录成功";
            header("refresh:1;url=/center");
        }else{
            echo "账号或密码错误";
        }
    }

    //个人中心
    public function center(Request $request){
        if(empty($_COOKIE['uid'])) {
            echo "请先登录";
        }
        $uid=$_COOKIE['uid'];
        $token=$_COOKIE['token'];
        $userInfo=UserModel::all();
        $data=[
            'uid'       => $uid,
            'token'     => $token,
            'data'      => $userInfo
        ];
        return view('user.center',$data);
    }

    public function center1(Request $request){
        $token = $_POST['token'];
        $uid = $_POST['uid'];
        $redis_key_web_token='web:str:u:token:'.$uid;
        $new_token = Redis::get($redis_key_web_token);
        if($token==$new_token){
            return 1;
        }else{
            return 2;
        }
    }

}
