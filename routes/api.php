<?php

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\IdentificationTypeController;
use App\Http\Controllers\ProductoUnitController;
use App\Http\Controllers\TypeProviderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidateFieldsController;
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

});*/

// Rutas para el controlador ProductoController
Route::get('/products', [ProductoController::class, 'index']); //mostrar todos los productos
Route::post('/products', [ProductoController::class, 'store']); //crear un producto
Route::put('/products/{id}', [ProductoController::class, 'update']); //actualizar un producto
Route::delete('/products/{id}', [ProductoController::class, 'destroy']); //eliminar un producto

// Rutas para el controlador ProviderController
Route::get('/providers', [ProviderController::class, 'index']); //mostrar todos los proveedores
Route::post('/providers', [ProviderController::class, 'store']); //crear un proveedor
Route::put('/providers/{id}', [ProviderController::class, 'update']); //actualizar un proveedor
Route::delete('/providers/{id}', [ProviderController::class, 'destroy']); //eliminar un proveedor

// Rutas para el controlador CategoryController
Route::get('/categories', [CategoryController::class, 'index']); //mostrar todos los proveedores
Route::post('/categories', [CategoryController::class, 'store']); //crear un proveedor
Route::put('/categories/{id}', [CategoryController::class, 'update']); //actualizar un proveedor
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']); //eliminar un proveedor

// Rutas para el controlador BrandController
Route::get('/brands', [BrandController::class, 'index']); //mostrar todos los proveedores
Route::post('/brands', [BrandController::class, 'store']); //crear un proveedor
Route::put('/brands/{id}', [BrandController::class, 'update']); //actualizar un proveedor
Route::delete('/brands/{id}', [BrandController::class, 'destroy']); //eliminar un proveedor

// Rutas para el controlador ProductoUnitController
Route::get('/units', [ProductoUnitController::class, 'index']); //mostrar todos las unidades
Route::post('/units', [ProductoUnitController::class, 'store']); //crear una unidad
Route::put('/units/{id}', [ProductoUnitController::class, 'update']); //actualizar una unidad
Route::delete('/units/{id}', [ProductoUnitController::class, 'destroy']); //eliminar una unidad

// Rutas para el controlador TypeProviderController
Route::get('/type-providers', [TypeProviderController::class, 'index']); //mostrar todos los tipo proveedor
Route::post('/type-providers', [TypeProviderController::class, 'store']); //crear un tipo proveedor
Route::put('/type-providers/{id}', [TypeProviderController::class, 'update']); //actualizar un tipo proveedor
Route::delete('/type-providers/{id}', [TypeProviderController::class, 'destroy']); //eliminar un tipo proveedor

// Rutas para el controlador TypeProviderController
Route::get('/identification_type', [IdentificationTypeController::class, 'index']); //mostrar todos los tipo proveedor
Route::post('/identification_type', [IdentificationTypeController::class, 'store']); //crear un tipo proveedor
Route::put('/identification_type/{id}', [IdentificationTypeController::class, 'update']); //actualizar un tipo proveedor
Route::delete('/identification_type/{id}', [IdentificationTypeController::class, 'destroy']); //eliminar un tipo proveedor

// Rutas para el controlador TypeProviderController
Route::get('/users', [UserController::class, 'index']); //mostrar todos los usuarios excepto los usuarios clientes
Route::post('/users', [UserController::class, 'store']); //crear un usuario
Route::put('/users/{id}', [UserController::class, 'update']); //actualizar un usuario
Route::delete('/users/{id}', [UserController::class, 'destroy']); //eliminar un usuario

Route::post('/validate/product/code', [ValidateFieldsController::class, 'validateProductCode']);
Route::post('/validate/product/name', [ValidateFieldsController::class, 'validateProductName']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout/{id}', [AuthController::class, 'logout']);
Route::post('/userinfo', [AuthController::class, 'infouser'])->middleware('auth:sanctum');



