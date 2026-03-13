<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMfaVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasMfaEnabled() && is_null($request->session()->get('mfa_verified'))) {
            return redirect()->route('two-factor.login');
        }

        return $next($request);
    }
}
