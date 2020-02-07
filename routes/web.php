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


Route::post('/test/reg','TestController@reg');
Route::post('/test/login','TestController@login');
Route::get('/test/info','TestController@info');

Route::post('/test/auth','TestController@auth');
Route::get('/test/checksign','TestController@md5test');