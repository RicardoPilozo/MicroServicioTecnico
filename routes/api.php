<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ServicioTecnicoController;
use App\Http\Controllers\ServicioTecnicoProductoController;

Route::get('/servicioTecnico', [ServicioTecnicoController::class, 'index']);
Route::post('/servicioTecnico', [ServicioTecnicoController::class, 'store']);


Route::get('/servicioTecnicoProducto', [ServicioTecnicoProductoController::class, 'index']);
Route::post('/servicioTecnicoProducto', [ServicioTecnicoProductoController::class, 'store']);

