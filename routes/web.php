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

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

//import
Route::get('/import/slack-users', 'SlackUsersController@importSlackUsers');

Route::prefix('webhook')->group(function () {
    Route::post('gitlab', 'WebhooksController@gitlab');
    Route::post('bitbucket', 'WebhooksController@bitbucket');
    Route::post('github', 'WebhooksController@github');
});

