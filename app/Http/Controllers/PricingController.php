<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PricingController extends Controller
{
    public function index()
    {
        try {
            $json = Storage::get('app/pricing/pricing.json');
            $plans = json_decode($json, true);

            return response()->json($plans);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Nie udało się załadować cennika.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
