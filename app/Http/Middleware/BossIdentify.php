<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BossIdentify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, \Closure $next, ...$allowedTypes): Response
    {
        $user = $request->user(); // Sanctum obtiene el usuario autenticado desde el token

        if (!$user) {
            return response()->json([
                'result' => false,
                'msg' => 'Usuario no autenticado.'
            ], 401);
        }

        // Si el tipo de usuario no está dentro de los roles permitidos, error
        if (!$user || !in_array($user->userType, $allowedTypes)) {
            return response()->json([
                'result' => false,
                'msg' => 'No tienes permiso para realizar esta acción.',
            ], 403);
        }

        return $next($request);
    }
}
