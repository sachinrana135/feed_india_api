<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/saveDonor', 'ApiController@saveDonor');
Route::post('/saveNeedier', 'ApiController@saveNeedier');
Route::get('/getUserById', 'ApiController@getUserById');
Route::get('/getUserByMobile', 'ApiController@getUserByMobile');
Route::post('/mapFcmIdToUser', 'ApiController@mapFcmIdToUser');
Route::post('/saveGroup', 'ApiController@saveGroup');

