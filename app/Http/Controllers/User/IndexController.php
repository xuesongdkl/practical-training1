<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
                'errcode'=>'50001',
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
            ];
        }else{
            $res_data=[
                'errcode'=>'5011',
                'msg'=>'账号或者密码错误'
            ];
        }
        return $res_data;
    }

    //登录页面
    public function login(){
        echo __METHOD__;
    }
}
