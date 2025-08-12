<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3' // e.g., "usd"
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100, // cents
                'currency' => strtolower($request->currency),
                'payment_method_types' => ['card'],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
