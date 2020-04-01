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
Route::get('/feedQuotes', 'ScheduledJobsController@feedQuotes');
Route::get('/sendPushmessage', 'ScheduledJobsController@sendPushmessage');
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/upgradeApp', 'AppWebviewController@upgradeApp');
Route::get('/maintenanceApp', 'AppWebviewController@maintenanceApp');
