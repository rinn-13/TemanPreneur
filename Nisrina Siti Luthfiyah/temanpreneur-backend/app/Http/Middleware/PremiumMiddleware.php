<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PremiumMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user->business || !$user->business->is_premium) {
            return response()->json(['message' => 'Premium business access required'], 403);
        }
        return $next($request);
    }
}