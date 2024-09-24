<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventScraperController;


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
//返回 welcome 視圖
Route::get('/', function () {
    return view('welcome');
});
//API請求返回JSON格式的所有事件數據
Route::get('api/events', [EventController::class, 'index']);
//網頁顯示所有事件
Route::get('events', [EventController::class, 'showEvents']);
Route::get('/fetch-event', [EventScraperController::class, 'fetchEvent']);