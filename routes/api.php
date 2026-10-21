<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentoController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/catalogo', function () {
            return response()->json([
                'mensaje' => 'Acceso autorizado. Bienvenido al catálogo.',
                'productos' => [
                    ['id' => 1, 'nombre' => 'Laptop Thunderobot'],
                    ['id' => 2, 'nombre' => 'Pasta Térmica TFX'],
                ],
            ]);
        })->middleware('scope:catalogo:read');

        Route::get('/documentos/{documento}', [DocumentoController::class, 'show']);
    });
});
