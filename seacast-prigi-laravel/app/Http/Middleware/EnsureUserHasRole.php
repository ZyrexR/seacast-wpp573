<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->guest(route('login'));
        }

        if (!empty($roles) && !$user->hasRole($roles)) {
            abort(403, 'Akses Ditolak: Peran [' . ($user->role ?? 'tamu') . '] tidak memiliki wewenang untuk mengakses sumber daya ini.');
        }

        return $next($request);
    }
}

