<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\QuestionSet;
use App\Services\PurchaseVerificationService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Imdhemy\AppStore\Exceptions\InvalidReceiptException;

class PurchaseController extends Controller
{
    public function __construct(private PurchaseVerificationService $verificationService)
    {
    }

    // Fixed price-tier catalog + derived store product ids, so the app never hardcodes tiers
    public function priceTiers()
    {
        $bundleId = config('price_tiers.bundle_id');

        $tiers = collect(config('price_tiers.tiers'))->map(function ($price, $tier) use ($bundleId) {
            return [
                'tier'               => $tier,
                'price'              => $price,
                'ios_product_id'     => $bundleId . '.' . $tier,
                'android_product_id' => $tier,
            ];
        })->values();

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
        $tierKeys = implode(',', array_keys(config('price_tiers.tiers')));

        $validator = Validator::make($request->all(), [
            'question_set_id' => 'required|exists:question_sets,id',
            'platform'        => 'required|in:ios,android',
            'product_id'      => 'required|string',
            'price_tier'      => 'required|string|in:' . $tierKeys,
            'receipt'         => 'required_if:platform,ios|string',
            'purchase_token'  => 'required_if:platform,android|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $questionSet = QuestionSet::findOrFail($request->question_set_id);
        $receiptOrToken = $request->platform === 'ios' ? $request->receipt : $request->purchase_token;

        try {
            $purchase = $this->verificationService->verifyAndRecord(
                $request->user(),
                $questionSet,
                $request->platform,
                $request->product_id,
                $request->price_tier,
                $receiptOrToken,
            );
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase could not be verified',
                'errors'  => $e->errors(),
            ], 422);
        } catch (InvalidReceiptException|GuzzleException $e) {
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

    public function myPurchases(Request $request)
    {
        $purchases = Purchase::with('questionSet.category')
            ->where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->orderBy('purchased_at', 'desc')
            ->get()
            ->map(function ($purchase) {
                return [
                    'id'                 => $purchase->id,
                    'question_set_id'    => $purchase->question_set_id,
                    'question_set_name'  => $purchase->questionSet->name ?? null,
                    'category_name'      => $purchase->questionSet->category->name ?? null,
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
