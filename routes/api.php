<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JwtAuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\EstablecimientoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

// 🔓 CRUD de usuarios (abierto)
Route::apiResource('users', UserController::class);


// Route::post('login', [AuthController::class, 'login']);
Route::post('login', [JwtAuthController::class, 'login']);


Route::middleware(['auth:api'])->group(function () {
    Route::get('perfil', [JwtAuthController::class, 'perfil']);
    Route::post('logout', [JwtAuthController::class, 'logout']);
});

// 📦 Rutas de Establecimientos
Route::get('establecimientos', [EstablecimientoController::class, 'index']);
Route::get('establecimientos/{id}', [EstablecimientoController::class, 'show']);
Route::post('establecimientos', [EstablecimientoController::class, 'store']);
Route::post('establecimiento-update/{id}', [EstablecimientoController::class, 'update']);
Route::delete('establecimientos/{id}', [EstablecimientoController::class, 'destroy']);
