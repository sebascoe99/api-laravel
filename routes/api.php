<?php

use App\Http\Controllers\ProductoController;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|

/*
Route::middleware(['auth:sanctum'])->group(function () {
    // Rutas para el controlador ProductoController
    //Route::get('/products', [ProductoController::class, 'index']); //mostrar todos los registros
    Route::post('/products', [ProductoController::class, 'store']); //crear un registro
    Route::put('/products/{id}', [ProductoController::class, 'update']); //actualizar un registro
    Route::delete('/products/{id}', 'App\Http\Controllers\ProductoController@destroy'); //eliminar un registro

    // Rutas para el controlador ProductoCategoriaController
    Route::post('/productCategory', 'App\Http\Controllers\ProductoCategoriaController@store'); //crear un registro
});*/
   // Rutas para el controlador ProductoController
    Route::get('/products', [ProductoController::class, 'index']); //mostrar todos los registros
    Route::post('/products', [ProductoController::class, 'store']); //crear un registro
    Route::put('/products/{id}', [ProductoController::class, 'update']); //actualizar un registro
    Route::delete('/products/{id}', 'App\Http\Controllers\ProductoController@destroy'); //eliminar un registro

    // Rutas para el controlador ProductoCategoriaController
    Route::post('/productCategory', 'App\Http\Controllers\ProductoCategoriaController@store'); //crear un registro

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', 'App\Http\Controllers\AuthController@register');
Route::post('/login', 'App\Http\Controllers\AuthController@login');
Route::post('/userinfo', 'App\Http\Controllers\AuthController@infouser')->middleware('auth:sanctum');



