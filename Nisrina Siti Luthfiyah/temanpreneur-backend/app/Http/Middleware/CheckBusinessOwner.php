<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckBusinessOwner
{
    public function handle(Request $request, Closure $next)
    {
        $business = $request->route('business');
        if ($business && $business->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not your business'], 403);
        }
        return $next($request);
    }
}