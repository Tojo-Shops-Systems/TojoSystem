<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddAuthHeaderFromCookie
{
    public function handle(Request $request, Closure $next)
    {
        // Si no hay header Authorization pero sí hay cookie 'token'
        if (!$request->bearerToken() && $request->hasCookie('token')) {
            $token = $request->cookie('token');
            // Inyectamos el token en el header para que Sanctum lo lea
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}