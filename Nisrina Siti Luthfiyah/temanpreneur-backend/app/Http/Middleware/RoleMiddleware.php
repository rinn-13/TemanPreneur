<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403, 'Unauthorized');
        }
        $hasRole = collect($roles)->contains(fn ($r) => $user->hasRole($r));
        if (! $hasRole) {
            abort(403, 'Unauthorized');
        }
        return $next($request);
    }
}