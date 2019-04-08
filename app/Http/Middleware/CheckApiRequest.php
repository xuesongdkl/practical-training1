<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class CheckApiRequest
{
    private $_api_data=[];
    private $_black_key='black_list';
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //先获取接口的数据，需要先解密
//        $this->_decrypt($request);
       $this->_RsaDecrypt($request);

        //访问次数限制
        $num=$this->_checkApiAccessCount();

        if($num['status']==1000){
            //验证签名
            $data=$this->_checkClientSign($request);
//            var_dump($data);die;
            //把解密的数据传递到控制器
            $request->request->replace($this->_api_data);
            //判断签名是否正确
            if($data['status']==1000){

                $response=$next($request);
                //后置操作 对返回的数据进行加密
//                echo "</pre>";
                $data=$response->original;
//                var_dump($data);die;
                $api_response=[];
                //使用对称加密对数据进行加密处理
//                $api_response['data']=$this->_encrypt($data);
                $api_response['data']=$this->_RsaEncrypt($data);
                //var_dump($api_response['data']);die;
                //生成签名，返回给客户端
                $api_response['sign']=$this->_createServerSign($data);
                return response($api_response);

            }else{
                return response($data);
            }
        }else{
            return response($num);
        }
    }

    //服务端返回的时候 返回一个签名
    private function _createServerSign($data){
        $app_id=$this->_getAppId();
//        var_dump($app_id);die;
        $all_app=$this->_getAllAppIdKey();
        //排序
        ksort($data);
        //变成a=1&b=2
        $sign_str=http_build_query($data).'&app_key='.$all_app[$app_id];
//        echo $sign_str;

        return md5($sign_str);
    }
    //加密
//    private function _encrypt($data){
//
//        if(!empty($data)){
//            $enc_data=openssl_encrypt(json_encode($data),'AES-256-CBC','dkllove',false,'0614668812076688');
//            return $enc_data;
//
////            $this->_api_data=json_decode($dec_data,true);
//        }
//    }
    //用非对称方法进行加密
    private function _RsaEncrypt($data){
        if(!empty($data)){
            $i=0;
            $all='';
            $str=json_encode($data);
            while($sub_str=substr($str,$i,117)){
                openssl_private_encrypt($sub_str,$enc_data,file_get_contents('./private.key'),OPENSSL_PKCS1_PADDING);
                $all.=base64_encode($enc_data);
                $i+=117;
            }
            return $all;
        }
    }

    //获取系统现有的appid和key
    private function _getAllAppIdKey(){
        //从数据库获得对应的数据
        return [
            md5(0614) =>  md5('12070614'),
            md5(2) =>  md5('2222222'),
            md5(3) =>  md5('3333333'),
        ];
    }

    //解密
    private function _decrypt($request){
        $data=$request->post('data');

        if(!empty($data)){
            $dec_data=openssl_decrypt($data,'AES-256-CBC','dkllove',false,'0614668812076688');
            $this->_api_data=json_decode($dec_data,true);
//            var_dump(json_decode($dec_data,true));
        }
    }

    //使用非对称进行解密
    private function _RsaDecrypt($request){
        $data=$request->post('data');
        if(!empty($data)){
            $i=0;
            $all='';
            while($sub_str=substr($data,$i,172)){
                $decode_data=base64_decode($sub_str);
                openssl_private_decrypt($decode_data,$dec_data,file_get_contents('./private.key'),OPENSSL_PKCS1_PADDING);
                $all.=$dec_data;
                $i+=172;
            }
            $this->_api_data=json_decode($all,true);
        }
    }

    //验证签名
    private function _checkClientSign($request){
//        var_dump($this->_api_data);die;
        if(!empty($this->_api_data)){
            //获取当前所有的app_id和key
            $map=$this->_getAppIdKey();
            if(!array_key_exists($this->_api_data['app_id'],$map)){
                return [
                    'status'   =>   1,
                    'msg'      =>   'check sign fail',
                    'data'     =>   []
                 ];
            }
//            var_dump($this->_api_data);die;
            ksort($this->_api_data);
            //变成字符串 拼接app_key
            $server_str=http_build_query($this->_api_data).'&app_key='.$map[$this->_api_data['app_id']];
//            var_dump($server_str);die;
            if(md5($server_str)!=$request['sign']){
                return [
                    'status'   =>   2,
                    'msg'      =>   'check sign fail1',
                    'data'     =>   []
                ];
            }
            return ['status' => 1000];
        }
    }

    //获取系统现有的appid和key
    private function _getAppIdKey(){
        //从数据库获得对应的数据
        return [
            md5(0614) =>  md5('12070614')
        ];
    }

    /*
     * 获取当前调用接口的appid**/
    private function _getAppId(){
        if(!empty($this->_api_data['app_id'])){
            return $this->_api_data['app_id'];
        }
    }

    //接口防刷
    private function _checkApiAccessCount(){
        //获取appid
        $app_id=$this->_getAppId();
//        echo $app_id;die;
        $black_key=$this->_black_key;
        //判断是否在黑名单中
        $join_black_name=Redis::zScore($black_key,$app_id);
        //不在黑名单
        if(empty($join_black_name)){
            $this->_addAppIdAccessCount();
            return ['status'=>1000];
        }else{
            //判断是否超过30min
            if(time()-$join_black_name>=10 ){
                Redis::zRemove($black_key,$app_id);
                $this->_addAppIdAccessCount();
            }else{
                return [
                    'status'   =>  2,
                    'msg'      =>  '暂时不能访问接口，请稍后再试',
                    'data'     =>   []
                ];
            }
        }
    }

    //记录appid对应的访问次数
    public function _addAppIdAccessCount(){
        $count=Redis::incr($this->_getAppId());
        if($count==1){
            Redis::expire($this->_getAppId(),60);
        }
        //大于等于100 加入黑名单
        if($count>=3){
            Redis::zAdd($this->_black_key,time(),$this->_getAppId());
            Redis::del($this->_getAppId());
            return [
                'status'   =>  3,
                'msg'      =>  '访问接口已达上限，暂时不能访问该接口,请稍后再试',
                'data'     =>   []
            ];
        }
    }
}
