<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Illuminate\Support\Facades\Route as Route;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Authentication End-Point
Route::group(['prefix' => 'auth'], function () use ($router) {
    $router->post('/register', 'Auth\\UserAuthController@register');
    $router->post('/login', 'Auth\\UserAuthController@login');
    $router->post('/logout', 'Auth\\UserAuthController@logout');
});

// With Authorization End-Point
Route::group(['middleware' => ['auth']], function ($router) {
    // User End-Point
    $router->get('/user', 'UserController@index');
    $router->get('/user/{id}', 'UserController@show');
    $router->put('/user/{id}', 'UserController@update');
    $router->delete('user/{id}', 'UserController@destroy');

    // Hotel End-Point
    $router->get('/hotel', 'HotelController@index');
    $router->get('/hotel/{id}', 'HotelController@show');
    $router->post('/hotel', 'HotelController@store');
    $router->put('/hotel/{id}', 'HotelController@update');
    $router->delete('hotel/{id}', 'HotelController@destroy');

    // Room End-Point
    $router->get('/room', 'roomController@index');
    $router->get('/room/{id}', 'roomController@show');
    $router->post('/room', 'roomController@store');
    $router->put('/room/{id}', 'roomController@update');
    $router->delete('room/{id}', 'roomController@destroy');

    // Reservation End-Point
    $router->get('/reservation', 'reservationController@index');
    $router->get('/reservation/{id}', 'reservationController@show');
    $router->post('/reservation', 'reservationController@store');
    $router->put('/reservation/{id}', 'reservationController@update');
    $router->delete('reservation/{id}', 'reservationController@destroy');

    // Bill End-Point
    $router->get('/bill', 'BillController@index');
    $router->get('/bill/{id}', 'BillController@show');
    $router->post('/bill', 'BillController@store');
    $router->put('/bill/{id}', 'BillController@update');
    $router->delete('bill/{id}', 'BillController@destroy');
});

// Without Authorization End-Point
Route::group(['prefix' => 'public'], function () use ($router) {
    // Hotel End-Point
    $router->get('/hotel', 'HotelController@index');
    $router->get('/hotel/{id}', 'HotelController@show');

    // Room End-Point
    $router->get('/room', 'RoomController@index');
    $router->get('/room/{id}', 'RoomController@show');
});