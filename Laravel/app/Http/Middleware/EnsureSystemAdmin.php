<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Check if user is a system admin
        if ($request->user()->user_type !== 'sys_admin') {
            return response()->json([
                'message' => 'Unauthorized. System admin access required.'
            ], 403);
        }

        // Check if system admin is active
        if (!$request->user()->is_active) {
            return response()->json([
                'message' => 'Your account is inactive. Please contact the administrator.'
            ], 403);
        }

        return $next($request);
    }
}