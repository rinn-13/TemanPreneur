<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

/**
 * Custom Authenticate middleware to avoid redirecting API requests
 * to a named `login` route which may not exist in this project.
 */
class Authenticate extends Middleware
{
    /**
     * Return the path the middleware should redirect to when the user is not authenticated.
     * Return null for API/JSON requests so framework will throw AuthenticationException
     * which is rendered as JSON by our bootstrap exception handler.
     */
    protected function redirectTo(Request $request)
    {
        // If the request expects JSON or is an API path, do not redirect.
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        // For non-API (web) requests, return a fallback URL (avoid named route lookup)
        // Use a simple URL to avoid RouteNotFoundException when 'login' route is not defined.
        return url('/login');
    }
}
