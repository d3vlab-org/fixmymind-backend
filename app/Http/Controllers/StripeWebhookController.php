<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use App\Models\User;

class StripeWebhookController extends CashierWebhookController
{
    public function handleInvoicePaid($payload)
    {
        $customerId = $payload['data']['object']['customer'] ?? null;
        $priceId = $payload['data']['object']['lines']['data'][0]['price']['id'] ?? null;

        if (!$customerId || !$priceId) {
            Log::warning('⚠️ Webhook: brak customer lub price ID');
            return response()->noContent();
        }

        $user = User::where('stripe_id', $customerId)->first();

        if (!$user) {
            Log::warning("⚠️ Webhook: nie znaleziono użytkownika o stripe_id {$customerId}");
            return response()->noContent();
        }

        // Wczytaj pricing.json i dopasuj plan
        $path = storage_path('app/pricing/pricing.json');
        $planName = 'Unknown';

        if (file_exists($path)) {
            $plans = json_decode(file_get_contents($path), true);
            foreach ($plans as $plan) {
                if (($plan['stripe_price_id'] ?? null) === $priceId) {
                    $planName = $plan['name'];
                    break;
                }
            }
        }

        $user->update(['current_plan' => $planName]);

        Log::info("✅ Subskrypcja aktywna: {$user->email} → {$planName}");

        return response()->noContent();
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Invalid Stripe webhook'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $userId = $session->metadata->user_id ?? null;

            if ($userId) {
                \App\Models\User::where('id', $userId)->update([
                    'subscription_status' => 'active',
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
