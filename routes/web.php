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

// Auth
Route::get('login', 'Auth\LoginController@showLoginForm' );
Route::post('login', 'Auth\LoginController@login');
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

Route::get('logout', function(){
	Auth::logout();
	Session::flush();
	return Redirect::to('/');
});

Route::group(['middleware' => 'web'], function () {
    Route::auth();

	// Home
	Route::get('/', 'HomeController@index')->name('home');

	// Brands
	Route::get('brands', 'BrandController@index')->name('brands');
	Route::get('brands/list', 'BrandController@get_list')->name('brands.list');
	Route::get('brands/create', 'BrandController@create')->name('brands.create');
	Route::post('brands/store', 'BrandController@store')->name('brands.store');
	Route::get('brands/destroy/{brand}', 'BrandController@destroy')->name('brands.destroy');

	// Clients
});


    
