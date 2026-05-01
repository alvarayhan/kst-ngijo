<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth('api')->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = auth('api')->user();
        if (!$user->role) {
            return response()->json(['success' => false, 'message' => 'User has no role assigned'], 403);
        }

        if (!in_array($user->role->name, $roles)) {
            return response()->json(['success' => false, 'message' => 'Forbidden - Insufficient permissions'], 403);
        }

        return $next($request);
    }
}
