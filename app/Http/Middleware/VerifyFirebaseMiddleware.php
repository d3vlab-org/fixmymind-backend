<?php
namespace App\Http\Middleware;;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;


class VerifyFirebaseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $auth = app('firebase.auth');

        $idToken = $request->bearerToken();

        if (!$idToken) {
            return response()->json(['message' => 'No Firebase token provided.'], 401);
        }

        try {
            $verifiedIdToken = $auth->verifyIdToken($idToken);
            $firebaseUid = $verifiedIdToken->claims()->get('sub');

            $user = \App\Models\User::where('firebase_uid', $firebaseUid)->first();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized - user not found.'], 401);
            }

            Auth::login($user); // Laravel auth login
            return $next($request);
        } catch (FailedToVerifyToken $e) {
            return response()->json(['message' => 'Invalid Firebase token.'], 401);
        }
    }
}
