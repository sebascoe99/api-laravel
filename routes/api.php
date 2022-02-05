<?php

use App\Http\Controllers\ProductoController;
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
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas para el controlador ProductoController
Route::get('/products', 'App\Http\Controllers\ProductoController@index'); //mostrar todos los registros
Route::post('/products', 'App\Http\Controllers\ProductoController@store'); //crear un registro
Route::put('/products/{id}', 'App\Http\Controllers\ProductoController@update'); //actualizar un registro
Route::delete('/products/{id}', 'App\Http\Controllers\ProductoController@destroy'); //eliminar un registro

// Rutas para el controlador ProductoCategoriaController
Route::post('/productCategory', 'App\Http\Controllers\ProductoCategoriaController@store'); //crear un registro

