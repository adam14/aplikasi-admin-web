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

/** Route API */
Route::group(['prefix' => 'api',  'middleware' => 'cors'], function () {

    Route::post('/register', 'Api\UserController@register');
    Route::post('/login', 'Api\UserController@login');

    Route::group(['middleware' => 'auth'], function() {
        // User
        Route::get('/users', 'Api\UserController@index');
        Route::get('/users/{id}', 'Api\UserController@detail');
        Route::put('/users/{id}', 'Api\UserController@update');
        Route::delete('/users/{id}', 'Api\UserController@delete');
    });

});
/** End */
