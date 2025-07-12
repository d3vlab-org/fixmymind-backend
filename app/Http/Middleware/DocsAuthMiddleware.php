<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DocsAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        
        $AUTH_USER = env('API_DOC_USER');
        $AUTH_PASS = env('API_DOC_PASSWORD');

        $hasAuth = $request->getUser() === $AUTH_USER && $request->getPassword() === $AUTH_PASS;

        if (!$hasAuth) {
            $headers = ['WWW-Authenticate' => 'Basic'];
            return response('Unauthorized', 401, $headers);
        }

        return $next($request);
    }
}