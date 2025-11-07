<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyUserType
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user(); // Sanctum obtiene el usuario autenticado desde el token

        if (!$user) {
            return response()->json([
                'result' => false,
                'msg' => 'Usuario no autenticado.'
            ], 401);
        }

        // Si el tipo de usuario no está dentro de los roles permitidos, error
        if (!in_array($user->userType, $roles)) {
            return response()->json([
                'result' => false,
                'msg' => 'Acceso denegado. No tienes permisos suficientes.'
            ], 403);
        }

        return $next($request);
    }
}
