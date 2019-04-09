<?php

namespace App\Http\Controllers\Exam;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
   public function index(Request $request){
       return [
           'status'  =>   1000,
           'msg'     =>   'success',
           'data'    =>   []
       ];
   }

   public function uploadImg(Request $request){
       if(empty($request->post('content'))) {
           return [
               'status' => 5000,
               'msg' => 'file not found',
               'data' => []
           ];
       }
        //指定文件存储路径
       $file_save_path=app_path().'/storage/uploads/'.date('Ym').'/';
       if(!is_dir($file_save_path)){
           mkdir($file_save_path,0777,true);
       }
       $file_name=time().rand(1000,9999).'.tmp';
       $byte=file_put_contents($file_save_path.$file_name,base64_decode($request->post('content')));
        if($byte>0){
            //查看文件格式
            $info=getimagesize($file_save_path.$file_name);
            if(!$info){
                return [
                    'status'   =>    50002,
                    'msg'      =>    '图片内容或格式不正确',
                    'data'     =>     []
                ];
            }
            //判断图片格式
            switch($info['mime']){
                case 'image/jpeg':
                    $new_file_name=str_replace('tmp','jpeg',$file_name);
                    break;
                case 'image/png':
                    $new_file_name=str_replace('tmp','png',$file_name);
                    break;
                default:
                    return ['status'   =>    50002, 'msg'      =>    '图片内容或格式不正确', 'data'     =>     []];
                    break;
            }
            //文件重新命名
            rename($file_save_path.$file_name,$file_save_path.$new_file_name);
            $api_response=[];
            $access_path=str_replace(app_path().'/storage','',$file_save_path);
            $api_response['access_path']=env('FILE_UPLOAD_URL').$access_path.$new_file_name;
            return [
                'status'  =>   1000,
                'msg'     =>   'success',
                'data'    =>   $api_response
            ];
        }
   }
}
