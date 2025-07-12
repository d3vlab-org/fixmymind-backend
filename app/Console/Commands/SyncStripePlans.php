<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;

class SyncStripePlans extends Command
{
    protected $signature = 'stripe:sync-plans';
    protected $description = 'Upload pricing plans from pricing.json to Stripe and update file with Price IDs';

    public function handle()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $path = storage_path('app/pricing/pricing.json');

        if (!file_exists($path)) {
            $this->error("Missing pricing.json at: $path");
            return;
        }

        $plans = json_decode(file_get_contents($path), true);
        $updatedPlans = [];

        foreach ($plans as $plan) {
            if ($plan['price'] === 0 || !empty($plan['stripe_price_id'])) {
                $this->info("Skipping: {$plan['name']}");
                $updatedPlans[] = $plan;
                continue;
            }

            $product = Product::create([
                'name' => $plan['name'],
            ]);

            $price = Price::create([
                'unit_amount' => $plan['price'],
                'currency' => $plan['currency'],
                'recurring' => ['interval' => $plan['interval']],
                'product' => $product->id,
            ]);

            $plan['stripe_price_id'] = $price->id;
            $updatedPlans[] = $plan;

            $this->info("✔ Created: {$plan['name']} | Product ID: {$product->id} | Price ID: {$price->id}");
        }

        file_put_contents($path, json_encode($updatedPlans, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info("✅ Updated pricing.json with new Stripe Price IDs");
    }
}
