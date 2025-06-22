<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->is_active) {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact support.'
            ], 403);
        }

        return $next($request);
    }
}
