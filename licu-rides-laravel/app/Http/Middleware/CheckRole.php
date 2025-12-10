<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión');
        }

        // Los administradores tienen acceso a todo
        if ($request->user()->role !== 'admin' && !in_array($request->user()->role, $roles)) {
            abort(403, 'No tienes permisos para acceder a esta página');
        }

        if ($request->user()->status !== 'active') {
            abort(403, 'Tu cuenta no está activa. Por favor verifica tu correo.');
        }

        return $next($request);
    }
}
