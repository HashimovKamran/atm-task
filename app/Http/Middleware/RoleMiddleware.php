<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Exceptions\{ApiAuthenticationException, ForbiddenAccessException};

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user instanceof User)
            throw new ApiAuthenticationException();

        $payload = JWTAuth::parseToken()->getPayload();
        $tokenType = $payload->get('typ');

        if ($tokenType !== 'user_session')
            throw new ForbiddenAccessException();

        $hasRequiredRole = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $hasRequiredRole = true;
                break;
            }
        }

        if (!$hasRequiredRole)
            throw new ForbiddenAccessException();

        return $next($request);
    }
}
