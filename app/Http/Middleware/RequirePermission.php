<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class RequirePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $claims = JWTAuth::parseToken()->getPayload()->get();
        $permissions = $claims['permissions'] ?? [];

        if (! is_array($permissions) || ! in_array($permission, $permissions, true)) {
            return response()->json(['mensaje' => 'Permiso insuficiente'], 403);
        }

        return $next($request);
    }
}
