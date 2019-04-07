<?php

namespace App\Http\Controllers\User;

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
        $ktoken='token:u:'.$user_data['uid'];
        $token=$token=str_random(32);
        Redis::hSet($ktoken,'app:token',$token);
        Redis::expire($ktoken,3600*24);
        if($user_data){
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
        $ktoken='token:uid:'.$userInfo['uid'];
        $token=$token=str_random(32);
        Redis::hSet($ktoken,'web:token',$token);
        Redis::expire($ktoken,3600*24);
        if($userInfo){
            echo "登录成功";
        }else{
            echo "账号或密码错误";
        }
    }
}
