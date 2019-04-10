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

    //得到图片验证码
    public function getVcodeUrl(){
        session_start();
        $sid=session_id();
        $url='http://vm.1807api.com/showvcode/'.$sid;
        $data=[
            'url'   => $url,
            'sid'   => $sid
        ];
        return [
            'status'  =>   1000,
            'msg'     =>   'success',
            'data'    =>   $data
        ];
    }

    //展示图片验证码
    public function showVcode(Request $request,$sid=''){
        session_id($sid);
        session_start();
        $rand=$this->randnum();
        //设置content-type
        header("Content-type: image/png");

        //创建一个100*30的画布
        $im = imagecreatetruecolor(130, 30);

        //创建几个颜色
        $white = imagecolorallocate($im, 30, 144, 255);
        $black = imagecolorallocate($im, 0, 0, 0);
        //填充画布的背景色
        imagefilledrectangle($im, 0, 0, 399, 29, $white);

        //字体文件
        $font = '/www/1807month4/calibri.ttf';
        //限制随机数
        $i=0;
        $len=strlen($rand);
        while($i<$len){
            //判断$rand[$i]是否为数字，若是，则让其旋转一定的角度
            if(is_numeric($rand[$i])){
                imagettftext($im, 20, rand(-30,30) , 10+20*$i, 20, $black, $font, $rand[$i]);
            }else{
                imagettftext($im, 20, 0 , 10+20*$i, 20, $black, $font, $rand[$i]);
            }
            $i++;
        }
        //与imagejpeg（）相比，使用imagepng（）可以使文本更清晰。
        imagepng($im);
        imagedestroy($im);
        exit();
    }

    public function randnum(){
        $type=rand(1,5);
        $a=rand(1,9);
        $b=rand(1,9);
        $c='';
        $text='';
        if($type==1){
            $rand=rand(1000,9999);
            $c=''. $rand;
            $text =$rand;
        }else if($type==2){
            $c="$a+$b=?";
            $text=$a+$b;
        }else if($type==3){
            if($a>$b){
                $c="$a-$b=?";
                $text=$a-$b;
            }else if($a==$b){
                $d=$a+rand(1,3);
                $c="$d-$b=?";
                $text=$d-$b;
            }else{
                $c="$b-$a=?";
                $text=$b-$a;
            }
        }else if($type==4){
            $c="$a*$b=?";
            $text=$a*$b;
        }else if($type==5){
            $d=$a*$b;
            $c="$d/$a=?";
            $text=$b;
        }
        $_SESSION['vcode']=$text;
        return $c;
    }

    //验证
    public function verify(Request $request){
        header("Access-Control-Allow-Origin: http://vm.test.com");
        header("Access-Control-Allow-Method:GET,POST");
        $vcode=$request->post('vcode');
        $sid=$request->post('sid');
        session_id($sid);
        session_start();
        if($_SESSION['vcode']==$vcode){
            return [
                'status'  =>   1000,
                'msg'     =>   'success',
                'data'    =>   []
            ];
        }else{
            return [
                'status'  =>   10,
                'msg'     =>   '验证码不正确',
                'data'    =>   []
            ];
        }
    }
}
