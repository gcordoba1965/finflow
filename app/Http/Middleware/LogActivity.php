<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    private const LOGGED_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            in_array($request->method(), self::LOGGED_METHODS) &&
            $request->user() &&
            $response->isSuccessful()
        ) {
            AuditLog::record(
                strtoupper($request->method()) . ':' . $request->path(),
                $request->user()->id,
                ['route' => $request->path()],
                true
            );
        }

        return $response;
    }
}
