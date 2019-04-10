<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//移动端登录
Route::post('/app/login','Test\IndexController@appLogin');
Route::post('/center2','Test\IndexController@center2');

//PC端登录
Route::get('/userlogin','Test\IndexController@login');
Route::post('/userlogin','Test\IndexController@doLogin');
Route::get('/center','Test\IndexController@center');
Route::post('/center1','Test\IndexController@center1');

Route::any('/test','Exam\IndexController@index')->middleware('check.api.request');
//文件上传
Route::any('/test/upload','Exam\IndexController@uploadImg')->middleware('check.api.request');
//图片验证码相关接口
Route::any('/getvcodeurl','Exam\IndexController@getVcodeUrl');
//展示验证码
Route::any('/showvcode/{sid}','Exam\IndexController@showVcode');
//验证
Route::any('/verify','Exam\IndexController@verify')->middleware('check.api.request');

