<?php

namespace App\Services;

use App\Models\AttemptPack;
use App\Models\Purchase;
use App\Models\QuestionSet;
use App\Models\QuestionSetPackage;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Imdhemy\AppStore\Exceptions\InvalidReceiptException;
use Imdhemy\AppStore\Jws\AppStoreJwsVerifier;
use Imdhemy\AppStore\Jws\Parser as JwsParser;
use Imdhemy\AppStore\ValueObjects\JwsTransactionInfo;
use Imdhemy\GooglePlay\Products\ProductPurchase;
use Imdhemy\Purchases\Facades\Product;

class PurchaseVerificationService
{
    public function __construct(private AttemptWalletService $walletService)
    {
    }

    /**
     * @param QuestionSet|QuestionSetPackage|AttemptPack|SubscriptionPlan $target
     * @param string $tierKey the store product key: a PriceTier.tier_key for question_set/package,
     *                        or an AttemptPack/SubscriptionPlan.product_key for those types
     *
     * @throws ValidationException
     * @throws InvalidReceiptException
     * @throws GuzzleException
     */
    public function verifyAndRecord(
        User $user,
        string $purchaseType,
        Model $target,
        string $platform,
        string $productId,
        string $tierKey,
        string $receiptOrToken
    ): Purchase {
        [$amount, $currency] = $this->resolvePrice($purchaseType, $target, $tierKey);

        $transactionId = match ($platform) {
            'ios'     => $this->verifyAppStore($receiptOrToken, $productId),
            'android' => $this->verifyGooglePlay($productId, $receiptOrToken),
            default   => throw ValidationException::withMessages(['platform' => 'Unsupported platform.']),
        };

        $targetColumn = $this->targetColumn($purchaseType);
        $isItemPurchase = in_array($purchaseType, ['question_set', 'package'], true);

        // Only the idempotent DB write happens inside the transaction. The Google
        // "consume" call below is a live network request to Google's API - if it were
        // inside this transaction, a transient failure there would roll back the
        // Purchase row we just verified as legitimately paid, leaving the user charged
        // but locked out. Consuming just re-arms the SKU as purchasable again on
        // Google's side; it isn't required for the user's own access, so it's run
        // after commit and its failure is logged, not fatal.
        $purchase = DB::transaction(function () use (
            $user, $purchaseType, $target, $platform, $productId, $tierKey,
            $transactionId, $amount, $currency, $targetColumn, $isItemPurchase
        ) {
            $purchase = Purchase::firstOrCreate(
                ['transaction_id' => $transactionId],
                [
                    'user_id'       => $user->id,
                    'purchase_type' => $purchaseType,
                    $targetColumn   => $target->id,
                    'platform'      => $platform,
                    'product_id'    => $productId,
                    'price_tier'    => $tierKey,
                    'price_paid'    => $amount,
                    'currency'      => $currency,
                    'status'        => 'completed',
                    'purchased_at'  => now(),
                    // Snapshot what this purchase grants so later admin edits to the item
                    // don't retroactively change what the customer already paid for.
                    'access_type'   => $isItemPurchase ? $target->access_type : null,
                    'access_value'  => $isItemPurchase ? $target->access_value : null,
                    'expires_at'    => ($isItemPurchase && $target->access_type === 'days')
                        ? now()->addDays($target->access_value)
                        : null,
                ]
            );

            if (!$purchase->wasRecentlyCreated
                && ($purchase->user_id !== $user->id || $purchase->{$targetColumn} !== $target->id)) {
                throw ValidationException::withMessages([
                    'transaction_id' => 'This transaction has already been used for a different purchase.',
                ]);
            }

            if ($purchase->wasRecentlyCreated) {
                $this->applySideEffects($purchaseType, $user, $target);
            }

            return $purchase;
        });

        if ($purchase->wasRecentlyCreated && $platform === 'android') {
            try {
                Product::googlePlay()->id($productId)->token($receiptOrToken)->consume();
            } catch (\Throwable $e) {
                \Log::error('Google Play consume failed after purchase was recorded: ' . $e->getMessage(), [
                    'purchase_id' => $purchase->id,
                    'product_id'  => $productId,
                ]);
            }
        }

        return $purchase;
    }

    private function targetColumn(string $purchaseType): string
    {
        return match ($purchaseType) {
            'question_set' => 'question_set_id',
            'package'      => 'package_id',
            'attempt_pack' => 'attempt_pack_id',
            'subscription' => 'subscription_plan_id',
            default        => throw ValidationException::withMessages(['purchase_type' => 'Unsupported purchase type.']),
        };
    }

    /**
     * @return array{0: int, 1: string} [amount, currency]
     */
    private function resolvePrice(string $purchaseType, Model $target, string $tierKey): array
    {
        if (in_array($purchaseType, ['question_set', 'package'], true)) {
            $priceTier = $target->priceTier;

            if (!$priceTier || $priceTier->tier_key !== $tierKey || !$priceTier->is_active) {
                throw ValidationException::withMessages(['price_tier' => 'Unknown or inactive price tier.']);
            }

            return [$priceTier->amount, $priceTier->currency];
        }

        if (in_array($purchaseType, ['attempt_pack', 'subscription'], true)) {
            if ($target->product_key !== $tierKey || !$target->is_active) {
                throw ValidationException::withMessages(['price_tier' => 'Unknown or inactive product.']);
            }

            return [$target->price, $target->currency];
        }

        throw ValidationException::withMessages(['purchase_type' => 'Unsupported purchase type.']);
    }

    private function applySideEffects(string $purchaseType, User $user, Model $target): void
    {
        if ($purchaseType === 'attempt_pack') {
            $this->walletService->credit($user, $target->attempts_count, 'purchase', $target);
            return;
        }

        if ($purchaseType === 'subscription') {
            UserSubscription::create([
                'user_id'              => $user->id,
                'subscription_plan_id' => $target->id,
                'starts_at'            => now(),
                'expires_at'           => $target->duration_days ? now()->addDays($target->duration_days) : null,
                'status'               => 'active',
            ]);
        }
    }

    /**
     * @throws ValidationException
     * @throws InvalidReceiptException
     * @throws GuzzleException
     */
    private function verifyAppStore(string $receiptData, string $expectedProductId): string
    {
        // StoreKit 2 clients (react-native-iap v15+) send a JWS-signed transaction:
        // three base64url segments separated by dots (header.payload.signature). Older
        // clients / Apple's legacy verifyReceipt send a single base64 app-receipt blob.
        if (substr_count($receiptData, '.') === 2) {
            return $this->verifyAppStoreJws($receiptData, $expectedProductId);
        }

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
     * Verify a StoreKit 2 JWS transaction and return its transaction id.
     *
     * @throws ValidationException
     */
    private function verifyAppStoreJws(string $jws, string $expectedProductId): string
    {
        try {
            $signature = JwsParser::toJws($jws);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['receipt' => 'Malformed App Store transaction.']);
        }

        $transaction = new JwsTransactionInfo($signature);
        $environment = $transaction->getEnvironment();

        // A transaction from a local StoreKit configuration file reports environment
        // "Xcode" and is signed by a throwaway local CA, not Apple's - the certificate
        // chain check can never pass for it. Accept those only when a dev/test backend
        // explicitly opts in; every other environment must pass full signature verification.
        if ($environment === 'Xcode') {
            if (! config('services.appstore.allow_xcode_env')) {
                throw ValidationException::withMessages([
                    'receipt' => 'Xcode StoreKit transactions are not accepted by this server.',
                ]);
            }
        } else {
            try {
                $verified = (new AppStoreJwsVerifier())->verify($signature);
            } catch (\Throwable $e) {
                $verified = false;
            }

            if (! $verified) {
                throw ValidationException::withMessages([
                    'receipt' => 'App Store transaction signature could not be verified.',
                ]);
            }
        }

        if ($transaction->getBundleId() !== config('price_tiers.bundle_id')) {
            throw ValidationException::withMessages([
                'receipt' => 'App Store transaction is for a different app.',
            ]);
        }

        if ($transaction->getProductId() !== $expectedProductId) {
            throw ValidationException::withMessages([
                'receipt' => 'App Store transaction does not match the requested product.',
            ]);
        }

        if ($transaction->getRevocationDate() !== null) {
            throw ValidationException::withMessages([
                'receipt' => 'This App Store transaction has been refunded or revoked.',
            ]);
        }

        $transactionId = $transaction->getTransactionId();
        if ($transactionId === null || $transactionId === '') {
            throw ValidationException::withMessages([
                'receipt' => 'App Store transaction is missing a transaction id.',
            ]);
        }

        return $transactionId;
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
