<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validar que la petición traiga email y password
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Buscar al usuario en la base de datos
        $user = User::where('email', $request->email)->first();

        // 3. Verificar si el usuario existe y la contraseña coincide
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'mensaje' => 'Credenciales incorrectas'
            ], 401);
        }

        // 4. Crear el token de seguridad (Sanctum)
        $token = $user->createToken('token-seguridad')->plainTextToken;

        // 5. Responder con el token
        return response()->json([
            'mensaje' => 'Login exitoso',
            'token' => $token,
            'usuario' => $user->name
        ]);
    }
}