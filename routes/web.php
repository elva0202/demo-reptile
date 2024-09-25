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
//返回 event 視圖 顯示前端頁面
Route::get('/', function () {
    return view('event');
});
// 處理AJAX請求路由App\Http\Controllers\
Route::any('/load-matches', [EventController::class, 'loadMatches']);
//API請求返回JSON格式的所有事件數據
Route::get('api/events', [EventController::class, 'index']);
//網頁顯示所有事件路由，渲染
Route::get('events', [EventController::class, 'showEvents']);
//顯示所有事件網頁
Route::get('/events', [EventController::class, 'showPage']);
// 調用 EventScraperController 的 fetchEvent 方法
Route::get('/fetch-events', [EventScraperController::class, 'fetchEvent']);



