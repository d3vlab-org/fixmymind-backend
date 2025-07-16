<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Http;

class SupabaseTokenValidator
{
    /**
     * Verify the JWT token using Supabase JWK and return the payload.
     *
     * @param  string  $token
     * @return object
     * @throws \Exception
     */
    public function validateToken(string $token): object
    {
        $jwksUrl = config('services.supabase.jwk_url');

        if (!$jwksUrl) {
            throw new \Exception('SUPABASE_JWK_URL environment variable is not set');
        }
        $response = Http::get($jwksUrl);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch JWK from Supabase');
        }

        $jwks = $response->json();
        $keys = JWK::parseKeySet($jwks);

        return JWT::decode($token, $keys);
    }
}
