<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\QuestionSet;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Imdhemy\AppStore\Exceptions\InvalidReceiptException;
use Imdhemy\GooglePlay\Products\ProductPurchase;
use Imdhemy\Purchases\Facades\Product;

class PurchaseVerificationService
{
    /**
     * @throws ValidationException
     * @throws InvalidReceiptException
     * @throws GuzzleException
     */
    public function verifyAndRecord(
        User $user,
        QuestionSet $questionSet,
        string $platform,
        string $productId,
        string $priceTier,
        string $receiptOrToken
    ): Purchase {
        $tiers = config('price_tiers.tiers');

        if (!array_key_exists($priceTier, $tiers)) {
            throw ValidationException::withMessages(['price_tier' => 'Unknown price tier.']);
        }

        $transactionId = match ($platform) {
            'ios'     => $this->verifyAppStore($receiptOrToken, $productId),
            'android' => $this->verifyGooglePlay($productId, $receiptOrToken),
            default   => throw ValidationException::withMessages(['platform' => 'Unsupported platform.']),
        };

        return DB::transaction(function () use ($user, $questionSet, $platform, $productId, $priceTier, $transactionId, $tiers) {
            $purchase = Purchase::firstOrCreate(
                ['transaction_id' => $transactionId],
                [
                    'user_id'         => $user->id,
                    'question_set_id' => $questionSet->id,
                    'platform'        => $platform,
                    'product_id'      => $productId,
                    'price_tier'      => $priceTier,
                    'price_paid'      => $tiers[$priceTier],
                    'currency'        => config('price_tiers.currency', 'JPY'),
                    'status'          => 'completed',
                    'purchased_at'    => now(),
                ]
            );

            if (!$purchase->wasRecentlyCreated
                && ($purchase->user_id !== $user->id || $purchase->question_set_id !== $questionSet->id)) {
                throw ValidationException::withMessages([
                    'transaction_id' => 'This transaction has already been used for a different purchase.',
                ]);
            }

            if ($purchase->wasRecentlyCreated && $platform === 'android') {
                Product::googlePlay()->id($productId)->token($receiptOrToken)->consume();
            }

            return $purchase;
        });
    }

    /**
     * @throws ValidationException
     * @throws InvalidReceiptException
     * @throws GuzzleException
     */
    private function verifyAppStore(string $receiptData, string $expectedProductId): string
    {
        $response = Product::appStore()
            ->receiptData($receiptData)
            ->verifyReceipt();

        $inApp = $response->getReceipt()?->getInApp() ?? [];

        foreach ($inApp as $transaction) {
            if ($transaction->getProductId() === $expectedProductId) {
                return $transaction->getTransactionId();
            }
        }

        throw ValidationException::withMessages([
            'receipt' => 'No matching transaction found in the App Store receipt.',
        ]);
    }

    /**
     * @throws ValidationException
     * @throws GuzzleException
     */
    private function verifyGooglePlay(string $productId, string $purchaseToken): string
    {
        $purchase = Product::googlePlay()
            ->id($productId)
            ->token($purchaseToken)
            ->get();

        if ($purchase->getPurchaseState() !== ProductPurchase::PURCHASE_STATE_PURCHASED) {
            throw ValidationException::withMessages([
                'purchase_token' => 'This purchase was not completed.',
            ]);
        }

        // The purchase token is unique per purchase and is the safest idempotency key -
        // orderId can be absent for some purchase types.
        return $purchaseToken;
    }
}
