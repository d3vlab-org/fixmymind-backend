<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Services\SupabaseTokenValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;

class VerifySupabaseToken
{
    protected SupabaseTokenValidator $tokenValidator;

    public function __construct(SupabaseTokenValidator $tokenValidator)
    {
        $this->tokenValidator = $tokenValidator;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $payload = $this->tokenValidator->validateToken($token);

            // Check if user UID is allowed
            $allowedUid = "1785dd9e-38b3-4fce-a2eb-8f468b1dac2b";
            if ($payload->sub !== $allowedUid) {
                return response()->json(['error' => 'under development, please check later'], 403);
            }

            $user = User::findBySupabaseUid($payload->sub);

            if (!$user) {
                // Create a new user with data from the token
                $email = $payload->email ?? '';
                $name = '';

                // Try to get name from various possible locations in the token
                if (isset($payload->user_metadata) && isset($payload->user_metadata->name)) {
                    $name = $payload->user_metadata->name;
                } elseif (isset($payload->name)) {
                    $name = $payload->name;
                } elseif (isset($payload->user_metadata) && isset($payload->user_metadata->full_name)) {
                    $name = $payload->user_metadata->full_name;
                }

                $user = User::create([
                    'supabase_uid' => $payload->sub,
                    'email' => $email,
                    'name' => $name,
                    'password' => bcrypt('default-password-' . time()),
                ]);
            }

            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            return $next($request);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token', 'message' => $e->getMessage()], 401);
        }
    }

    /**
     * Extract token from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return null;
        }

        return substr($header, 7);
    }

}
