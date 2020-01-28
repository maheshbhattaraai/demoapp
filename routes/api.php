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

Route::post('/register','API\UserRegisterController@userRegister');

Route::post('/login','API\UserRegisterController@login');

Route::post('/customer','API\UserRegisterController@customerRegister')->middleware('auth:api','scope:admin,superadmin');

Route::post('/change-password','API\UserRegisterController@changePassword')->middleware('auth:api','scope:superadmin,admin,customer');

Route::get('/list-customer','API\UserRegisterController@listOfUser')->middleware('auth:api','scope:admin,superadmin');

Route::post('/customer-data/{id}','API\CustomerController@store')->middleware('auth:api','scope:admin,superadmin');

Route::get('/customer-data/{id}','API\CustomerController@getuserDetailForAdmin')->middleware('auth:api','scope:admin,customer,superadmin');

Route::get('/customer-data','API\CustomerController@getuserDetailForUser')->middleware('auth:api','scope:customer');

Route::post('/verify-account','API\UserRegisterController@userVerified');

Route::post('/setnewpassword','API\UserRegisterController@setNewPassword');

Route::get('/data/{id}/delete','API\CustomerController@removeAccountData')->middleware('auth:api','scope:admin,superadmin');

Route::post('/data/{id}/update','API\CustomerController@update')->middleware('auth:api','scope:admin,superadmin');





