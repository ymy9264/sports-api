<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

Route::get('hello/:name', 'index/hello');
Route::get('api/matches', 'Matches/index');
Route::get('api/players', 'Players/index');
Route::get('api/teams', 'Teams/index');

Route::post('api/matches/save','Matches/save');
Route::post('api/matches/update','Matches/update');
Route::post('api/matches/delete','Matches/delete');

Route::post('api/players/save','Players/save');
Route::post('api/players/update','Players/update');
Route::post('api/players/delete','Players/delete');

Route::post('api/teams/save','Teams/save');
Route::post('api/teams/update','Teams/update');
Route::post('api/teams/delete','Teams/delete');

Route::post('api/login','Auth/login');
