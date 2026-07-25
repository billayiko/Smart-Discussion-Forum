<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiUserIsNotBlacklisted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isBlacklisted()) {
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Your account has been suspended due to inactivity. Please contact an administrator.',
            ], 403);
        }

        return $next($request);
    }
}
