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

Route::post('/getLanguages', 'ApiController@getLanguages');
Route::post('/getReportReasons', 'ApiController@getReportReasons');
Route::post('/getCountries', 'ApiController@getCountries');
Route::post('/getCategories', 'ApiController@getCategories');
Route::post('/getCanvasThemes', 'ApiController@getCanvasThemes');
Route::post('/getAuthors', 'ApiController@getAuthors');
Route::post('/getAuthor', 'ApiController@getAuthor');
Route::post('/getQuote', 'ApiController@getQuote');
Route::post('/getQuotes', 'ApiController@getQuotes');
Route::post('/getComments', 'ApiController@getComments');
Route::post('/reportQuote', 'ApiController@reportQuote');
Route::post('/reportComment', 'ApiController@reportComment');
Route::post('/likeQuote', 'ApiController@likeQuote');
Route::post('/followAuthor', 'ApiController@followAuthor');
Route::post('/saveUser', 'ApiController@saveUser');
Route::post('/updateAuthor', 'ApiController@updateAuthor');
Route::post('/updateProfileImage', 'ApiController@updateProfileImage');
Route::post('/updateCoverImage', 'ApiController@updateCoverImage');
Route::post('/updateUserCountry', 'ApiController@updateUserCountry');
Route::post('/saveComment', 'ApiController@saveComment');
Route::post('/saveQuote', 'ApiController@saveQuote');
Route::post('/getUserFeed', 'ApiController@getUserFeed');
Route::post('/deleteQuote', 'ApiController@deleteQuote');
Route::post('/mapFcmIdToUser', 'ApiController@mapFcmIdToUser');
Route::post('/getStartUpConfig', 'ApiController@getStartUpConfig');

