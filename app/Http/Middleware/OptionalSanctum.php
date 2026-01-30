<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class OptionalSanctum
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->bearerToken();

        if ($header) {
            $token = PersonalAccessToken::findToken($header);
            if ($token) {
                Auth::login($token->tokenable);
            }
        }

        return $next($request);
    }
}
