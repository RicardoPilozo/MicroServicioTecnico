<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ServicioTecnicoController;

Route::get('/servicioTecnico', [ServicioTecnicoController::class, 'index']);

