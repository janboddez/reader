<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'HomeController@index')->name('home');

// To do: where possible, better group these.
Route::get('channel/{uid}/{source}/{entry}', 'TimelineController@show')->name('entry');
Route::get('channel/{uid}/{source}', 'TimelineController@show')->name('source');
Route::get('channel/{uid}', 'TimelineController@show')->name('channel');

Route::post('channels/reload', 'HomeController@reload');

Route::post('microsub/mark-read', 'HomeController@markRead');
Route::post('microsub/mark-all-read', 'HomeController@markAllRead');
Route::post('microsub/mark-unread', 'HomeController@markUnread');
Route::post('microsub/remove', 'HomeController@remove');
Route::post('microsub/unfollow', 'HomeController@unfollow');
Route::post('microsub/fetch-original', 'HomeController@fetchOriginal');

Route::post('micropub', 'HomeController@micropub');

Route::get('login', 'LoginController@login')->name('login');
Route::post('login', 'LoginController@start');
Route::get('login/callback', 'LoginController@callback')->name('login_callback');
Route::get('logout', 'LoginController@logout')->name('logout');

Route::get('settings', 'HomeController@settings')->name('settings');
Route::post('settings', 'HomeController@store');
