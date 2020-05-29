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
//    \Illuminate\Support\Facades\Log::getLogger('test')->info(1);
//    \Illuminate\Support\Facades\Log::getLogger('test222')->info(2);
//    $response = \App\Facades\Http::post('/api/wechat/getwebtitle')->toArray();
//
//    dd($response);
    return view('welcome');
});
