<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);
// Ruta privada: Requiere el token de Sanctum
Route::middleware('auth:sanctum')->get('/catalogo', function () {
    return response()->json([
        'mensaje' => 'Acceso autorizado. Bienvenido al catálogo.',
        'productos' => [
            ['id' => 1, 'nombre' => 'Laptop Thunderobot'],
            ['id' => 2, 'nombre' => 'Pasta Térmica TFX']
        ]
    ]);
});