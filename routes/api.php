<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PricingController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\VoiceSessionController;
use App\Http\Controllers\VoiceMessageController;
use App\Http\Controllers\TestController;





Route::get('/checkout/success', fn () => view('checkout-success'));
Route::get('/checkout/cancel', fn () => view('checkout-cancel'));

// Public routes
Route::get('/pricing', [PricingController::class, 'index']);

// Temporarily disabled Firebase authentication until credentials are configured
// Route::middleware(['firebase'])->group(function () {

    // Mock subscribe endpoint (no authentication required for now)
    Route::post('/subscribe', function (Request $request) {
        return response()->json([
            'message' => 'Subscription request received',
            'price_id' => $request->input('price_id'),
            'user_id' => 'temp-user-123',
            'status' => 'pending',
            'checkout_url' => 'https://checkout.stripe.com/temp-session',
            'note' => 'This is a temporary endpoint. Real subscription processing will be implemented with proper authentication and Stripe integration.',
            'timestamp' => now()->toISOString()
        ]);
    });

    // Voice sessions (temporarily unprotected)
    Route::apiResource('voice-sessions', VoiceSessionController::class);
    Route::apiResource('voice-messages', VoiceMessageController::class);

    // Psychometric tests (temporarily unprotected)
    Route::get('/tests', [TestController::class, 'index']);
    Route::get('/tests/{test}', [TestController::class, 'show']);
    Route::post('/tests/{test}/submit', [TestController::class, 'submit']);

    // Stripe routes (commented out until StripeController is implemented)
    // Route::post('/subscribe', [StripeController::class, 'subscribe']);
    // Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
// });


// Working /me endpoint (currently returns mock data until authentication is configured)
Route::get('/me', function () {
    // Return a mock user for now
    return response()->json([
        'id' => 'user-123',
        'name' => 'Current User',
        'email' => 'user@fixmymind.org',
        'firebase_uid' => null,
        'created_at' => now()->subDays(30)->toISOString(),
        'updated_at' => now()->toISOString(),
        'authenticated' => false,
        'note' => 'This endpoint will return real user data once authentication is properly configured.'
    ]);
});
