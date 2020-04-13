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
Route::post('/saveMember', 'ApiController@saveMember');
Route::post('/saveGroup', 'ApiController@saveGroup');
Route::post('/saveComment', 'ApiController@saveComment');
Route::post('/mapFcmIdToUser', 'ApiController@mapFcmIdToUser');

Route::get('/getUserById', 'ApiController@getUserById');
Route::get('/getNeedier', 'ApiController@getNeedier');
Route::get('/getUserByMobile', 'ApiController@getUserByMobile');
Route::get('/getNearByGroups', 'ApiController@getNearByGroups');
Route::get('/getNearByUsers', 'ApiController@getNearByUsers');
Route::get('/getGroupNeedierItems', 'ApiController@getGroupNeedierItems');
Route::get('/getGroupMember', 'ApiController@getGroupMember');
Route::get('/getComments', 'ApiController@getComments');
Route::get('/getNeedierItemStatusTypes', 'ApiController@getNeedierItemStatusTypes');
Route::get('/getStartUpConfig', 'ApiController@getStartUpConfig');

Route::put('/updateNeedierItemStatus', 'ApiController@updateNeedierItemStatus');
Route::put('/updateDonor', 'ApiController@updateDonor');

