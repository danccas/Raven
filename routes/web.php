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

// Example Routes
Route::view('/', 'landing');
Route::match(['get', 'post'], '/dashboard', function(){
    return view('dashboard');
});
Route::view('/pages/slick', 'pages.slick');
Route::view('/pages/datatables', 'pages.datatables');

Route::resource('clientes', 'ClientesController');

Route::post('clientes/{cliente}/add-representante', 'ClientesController@addRepresentante');
Route::get('clientes/{cliente}/del-representante/{contacto}', 'ClientesController@delRepresentante');

Route::post('clientes/{cliente}/add-producto', 'ClientesController@addProducto');
Route::get('clientes/{cliente}/del-producto/{producto}', 'ClientesController@delProducto');

Route::resource('agencias', 'AgenciaController');

Route::resource('contratos', 'ContratoController');


//Route::resource('tarifas', 'TarifasController');
//Route::resource('ordenes', 'OrdenesController');


/* Clientes */
//Route::get('clientes', 'ClientesController@index');
//Route::get('clientes/create', 'ClientesController@create');
//Route::post('clientes/store', 'ClientesController@store');
//Route::post('clientes/add-representante', 'ClientesController@addRepresentante');
//Route::post('clientes/add-producto', 'ClientesController@addProducto');

/* Tarifas */
Route::get('tarifas','TarifasController@index');
Route::get('tarifas/create','TarifasController@create');
Route::post('tarifas/store','TarifasController@store');
Route::get('tarifas/add-programa','TarifasController@addPrograma');
Route::get('tarifas/add-bloque','TarifasController@addPrograma');

/* Orden */
Route::get('/orden/nuevo','OrdenesController@index');



Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    return "Cache is cleared";
});
