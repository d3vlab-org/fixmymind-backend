<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Inicjalizuj subskrypcję Stripe Checkout
     */
    public function subscribe(Request $request)
    {
        try {
            $user = $request->user();
            $priceId = $request->input('price_id');

            if (!$priceId) {
                return response()->json(['error' => 'Brak price_id'], 400);
            }

            $checkout = $user->newSubscription('default', $priceId)->checkout([
                'success_url' => config('app.url') . '/subscription/success',
                'cancel_url' => config('app.url') . '/subscription/cancel',
            ]);

            return response()->json(['checkout_url' => $checkout->url]);

        } catch (\Throwable $e) {
            \Log::error('❌ Stripe checkout error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Wystąpił błąd płatności.'], 500);
        }
    }
    /**
     * Sprawdź status subskrypcji użytkownika
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $sub = $user->subscription('default');

        return response()->json([
            'active' => $sub && $sub->active(),
            'status' => $sub?->stripe_status,
            'plan' => $sub?->stripe_price,
            'ends_at' => $sub?->ends_at,
        ]);
    }

    /**
     * Anuluj subskrypcję
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        $sub = $user->subscription('default');

        if ($sub && $sub->active()) {
            $sub->cancel();
            return response()->json(['message' => 'Subskrypcja została anulowana.']);
        }

        return response()->json(['message' => 'Brak aktywnej subskrypcji.'], 400);
    }
}
