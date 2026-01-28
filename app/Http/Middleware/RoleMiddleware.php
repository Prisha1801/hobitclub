<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
     public function handle(Request $request, Closure $next, string $roles)
    {
        $user = $request->user();

        if (!$user || !$user->role) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        // 🔥 Convert "admin,coordinator" → ["admin","coordinator"]
        $allowedRoles = array_map('trim', explode(',', $roles));

        if (!in_array($user->role->slug, $allowedRoles, true)) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        return $next($request);
    }
}
