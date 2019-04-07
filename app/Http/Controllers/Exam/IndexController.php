<?php

namespace App\Http\Controllers\Exam;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
   public function index(){
       return [
           'status'  =>   1000,
           'msg'     =>   'success',
           'data'    =>   []
       ];
   }
}
