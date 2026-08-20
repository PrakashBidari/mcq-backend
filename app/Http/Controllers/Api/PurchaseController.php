<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttemptPack;
use App\Models\PriceTier;
use App\Models\Purchase;
use App\Models\QuestionSet;
use App\Models\QuestionSetPackage;
use App\Models\SubscriptionPlan;
use App\Services\PurchaseVerificationService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Imdhemy\AppStore\Exceptions\InvalidReceiptException;

class PurchaseController extends Controller
{
    public function __construct(private PurchaseVerificationService $verificationService)
    {
    }

    // Admin-managed price-tier catalog + derived store product ids. Same response
    // shape as the old config-driven version, so existing mobile builds keep working.
    public function priceTiers()
    {
        $bundleId = config('price_tiers.bundle_id');

        $tiers = PriceTier::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PriceTier $tier) => [
                'tier'               => $tier->tier_key,
                'price'              => (float) $tier->amount,
                'ios_product_id'     => $tier->iosProductId(),
                'android_product_id' => $tier->androidProductId(),
            ])->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'bundle_id' => $bundleId,
                'tiers'     => $tiers,
            ],
        ]);
    }

    public function verify(Request $request)
    {
        $purchaseType = $request->input('purchase_type', 'question_set');

        $validator = Validator::make($request->all(), [
            'purchase_type'         => 'nullable|in:question_set,package,attempt_pack,subscription',
            'question_set_id'       => 'required_if:purchase_type,question_set|exists:question_sets,id',
            'package_id'            => 'required_if:purchase_type,package|exists:question_set_packages,id',
            'attempt_pack_id'       => 'required_if:purchase_type,attempt_pack|exists:attempt_packs,id',
            'subscription_plan_id'  => 'required_if:purchase_type,subscription|exists:subscription_plans,id',
            'platform'              => 'required|in:ios,android',
            'product_id'            => 'required|string',
            'price_tier'            => 'required|string',
            'receipt'               => 'required_if:platform,ios|string',
            'purchase_token'        => 'required_if:platform,android|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $target = $this->resolveTarget($purchaseType, $request);
        $receiptOrToken = $request->platform === 'ios' ? $request->receipt : $request->purchase_token;

        try {
            $purchase = $this->verificationService->verifyAndRecord(
                $request->user(),
                $purchaseType,
                $target,
                $request->platform,
                $request->product_id,
                $request->price_tier,
                $receiptOrToken,
            );
        } catch (ValidationException $e) {
            \Log::error('Purchase verification rejected: ' . json_encode($e->errors()), [
                'user_id'       => $request->user()->id,
                'platform'      => $request->platform,
                'product_id'    => $request->product_id,
                'purchase_type' => $purchaseType,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Purchase could not be verified',
                'errors'  => $e->errors(),
            ], 422);
        } catch (InvalidReceiptException|GuzzleException $e) {
            \Log::error('Purchase verification failed: ' . $e->getMessage(), [
                'user_id'       => $request->user()->id,
                'platform'      => $request->platform,
                'product_id'    => $request->product_id,
                'purchase_type' => $purchaseType,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Purchase verification failed',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Purchase verified',
            'data'    => $purchase,
        ]);
    }

    private function resolveTarget(string $purchaseType, Request $request): Model
    {
        return match ($purchaseType) {
            'question_set' => QuestionSet::findOrFail($request->question_set_id),
            'package'      => QuestionSetPackage::findOrFail($request->package_id),
            'attempt_pack' => AttemptPack::findOrFail($request->attempt_pack_id),
            'subscription' => SubscriptionPlan::findOrFail($request->subscription_plan_id),
        };
    }

    public function myPurchases(Request $request)
    {
        $purchases = Purchase::with(['questionSet.category', 'package', 'attemptPack', 'subscriptionPlan'])
            ->where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->orderBy('purchased_at', 'desc')
            ->get()
            ->map(function (Purchase $purchase) {
                $label = match ($purchase->purchase_type) {
                    'question_set' => $purchase->questionSet->name ?? null,
                    'package'      => $purchase->package->name ?? null,
                    'attempt_pack' => $purchase->attemptPack->name ?? null,
                    'subscription' => $purchase->subscriptionPlan->name ?? null,
                    default        => null,
                };

                return [
                    'id'                 => $purchase->id,
                    'purchase_type'      => $purchase->purchase_type,
                    'question_set_id'    => $purchase->question_set_id,
                    'question_set_name'  => $purchase->questionSet->name ?? null,
                    'category_name'      => $purchase->questionSet->category->name ?? null,
                    'item_name'          => $label,
                    'price_paid'         => (float) $purchase->price_paid,
                    'currency'           => $purchase->currency,
                    'purchased_at'       => $purchase->purchased_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $purchases,
        ]);
    }
}
