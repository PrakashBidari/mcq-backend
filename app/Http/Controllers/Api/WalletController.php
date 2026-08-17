<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttemptPack;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Services\AttemptWalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(private AttemptWalletService $walletService)
    {
    }

    public function balance(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => ['balance' => $this->walletService->balance($request->user()->id)],
        ]);
    }

    public function attemptPacks()
    {
        $packs = AttemptPack::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (AttemptPack $pack) => [
                'id' => $pack->id,
                'name' => $pack->name,
                'attempts_count' => $pack->attempts_count,
                'price' => (float) $pack->price,
                'currency' => $pack->currency,
                'product_key' => $pack->product_key,
                'ios_product_id' => $pack->iosProductId(),
                'android_product_id' => $pack->androidProductId(),
            ]);

        return response()->json([
            'success' => true,
            'data' => ['bundle_id' => config('price_tiers.bundle_id'), 'packs' => $packs],
        ]);
    }

    public function subscriptionPlans()
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SubscriptionPlan $plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'duration_days' => $plan->duration_days,
                'is_lifetime' => $plan->isLifetime(),
                'price' => (float) $plan->price,
                'currency' => $plan->currency,
                'product_key' => $plan->product_key,
                'ios_product_id' => $plan->iosProductId(),
                'android_product_id' => $plan->androidProductId(),
            ]);

        return response()->json([
            'success' => true,
            'data' => ['bundle_id' => config('price_tiers.bundle_id'), 'plans' => $plans],
        ]);
    }

    public function mySubscription(Request $request)
    {
        $subscription = UserSubscription::with('plan')
            ->where('user_id', $request->user()->id)
            ->active()
            ->latest('id')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $subscription ? [
                'plan_name' => $subscription->plan->name,
                'starts_at' => $subscription->starts_at,
                'expires_at' => $subscription->expires_at,
                'is_lifetime' => is_null($subscription->expires_at),
            ] : null,
        ]);
    }
}
