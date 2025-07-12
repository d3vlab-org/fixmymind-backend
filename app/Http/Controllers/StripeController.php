<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\User;

class StripeController extends Controller
{
    public function subscribe(Request $request)
    {
        $user = $request->user();

        $priceId = $request->input('price_id');

        if (!$priceId) {
            return response()->json(['error' => 'Brak ID planu Stripe'], 422);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $checkoutSession = Session::create([
                'customer_email' => $user->email ?? null,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => config('app.url') . '/checkout/success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => config('app.url') . '/checkout/cancel',
            ]);

            return response()->json([
                'message' => 'Subscription request created',
                'checkout_url' => 'https://buy.stripe.com/test_00w00lc86gqg9UP53Rf3a00',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Stripe error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
