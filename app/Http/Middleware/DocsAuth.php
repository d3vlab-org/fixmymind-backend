<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DocsAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = env('APIDOC_TOKEN'); // Tutaj ustaw właściwy token lub użyj .env

        if ($request->header('Authorization') !== 'Bearer ' . $token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}