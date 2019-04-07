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

Route::post('/test','Exam\IndexController@index')->middleware('check.api.request');

//移动端登录
Route::post('/app/login','User\IndexController@appLogin');

Route::get('/userlogin','User\IndexController@login');
Route::post('/userlogin','User\IndexController@doLogin');
Route::get('/center','User\IndexController@center');
Route::post('/center1','User\IndexController@center1');

