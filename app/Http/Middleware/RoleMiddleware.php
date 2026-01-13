<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            abort(403, 'Anda harus login terlebih dahulu.');
        }

        if (!in_array($request->user()->role, $roles)) {
            abort(403, 'Akses ditolak. Role tidak sesuai.');
        }

        return $next($request);
    }
}