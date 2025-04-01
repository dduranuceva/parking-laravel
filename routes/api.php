<?php

use App\Http\Controllers\EstablecimientoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes for Establecimientos
// Route::apiResource('establecimientos', EstablecimientoController::class);
// API Routes for Establecimientos
Route::get('establecimientos', [EstablecimientoController::class, 'index']);
Route::get('establecimientos/{id}', [EstablecimientoController::class, 'show']);
Route::post('establecimientos', [EstablecimientoController::class, 'store']);
Route::post('establecimiento-update/{id}', [EstablecimientoController::class, 'update']);
Route::delete('establecimientos/{id}', [EstablecimientoController::class, 'destroy']);