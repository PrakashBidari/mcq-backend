<?php

namespace App\Services;

use App\Models\AttemptPack;
use App\Models\Purchase;
use App\Models\QuestionSet;
use App\Models\QuestionSetPackage;
use App\Models\SubscriptionPlan;
use App\Models\TrialUsage;
use App\Models\User;
use App\Models\UserSubscription;

class AccessControlService
{
    public function __construct(
        private TrialService $trialService,
        private AttemptWalletService $walletService,
    ) {
    }

    /**
     * Resolve access AND consume the trial-use/wallet-attempt it was granted through.
     * Use this only at the moment a user actually starts a quiz session - never in a
     * listing/badge context, since it has side effects.
     */
    public function resolveAccess(?User $user, QuestionSet|QuestionSetPackage $item): array
    {
        return $this->evaluate($user, $item, consume: true);
    }

    /**
     * Read-only check for listings/badges (e.g. "is this owned/on trial?") - same
     * priority logic as resolveAccess() but never consumes a trial use or wallet attempt.
     */
    public function previewAccess(?User $user, QuestionSet|QuestionSetPackage $item): array
    {
        return $this->evaluate($user, $item, consume: false);
    }

    /**
     * Consume exactly one unit of whatever grant the user is taking this quiz under -
     * an attempts purchase, an attempts trial, or a wallet attempt. Call this ONCE,
     * when a quiz is actually completed, NOT when the questions are fetched: the client
     * can hit GET /question-set/{id} several times for a single real start (screen
     * re-render, network retry, stacked post-purchase "Start Quiz" prompts), and
     * consuming there burned an attempt every time. No-op for free content, day-based
     * grants, and subscriptions.
     */
    public function consumeForCompletedQuiz(?User $user, QuestionSet|QuestionSetPackage $item): void
    {
        if (!$user || !$item->is_paid) {
            return;
        }

        if ($item instanceof QuestionSet && $item->package_id) {
            $this->consumeForCompletedQuiz($user, $item->package ?? $item->package()->firstOrFail());
            return;
        }

        if ($this->trialService->hasAvailableTrial($user, $item)) {
            if (($item->trial_type ?? null) === 'attempts') {
                $this->trialService->consumeTrial($user, $item);
            }
            return;
        }

        $activePurchase = $item instanceof QuestionSetPackage
            ? Purchase::activePackagePurchase($user->id, $item->id)
            : Purchase::activeQuestionSetPurchase($user->id, $item->id);

        if ($activePurchase) {
            if ($activePurchase->access_type === 'attempts') {
                $activePurchase->increment('attempts_used');
            }
            return;
        }

        if (UserSubscription::where('user_id', $user->id)->active()->exists()) {
            return;
        }

        if ($this->walletService->balance($user->id) > 0) {
            $this->walletService->debit($user, 1, 'consume', $item);
        }
    }

    /**
     * Read-only description of the grant the user currently holds for this item, for
     * showing "attempt 2 of 3" / "expires in 5 days" while they take the quiz. Never
     * consumes anything. Returns null for free content, guests, or no active access
     * (the paywall path is handled separately by evaluate()).
     *
     * Shape: ['kind' => 'attempts'|'trial_attempts'|'days'|'trial_days'|'subscription'|'unlimited'|'wallet', ...]
     *  - attempts / trial_attempts: attempts_used, attempts_total, attempts_remaining
     *  - days / trial_days:         expires_at (ISO 8601), days_remaining
     *  - wallet:                    attempts_remaining
     */
    public function accessSummary(?User $user, QuestionSet|QuestionSetPackage $item): ?array
    {
        if ($item instanceof QuestionSet && $item->package_id) {
            return $this->accessSummary($user, $item->package ?? $item->package()->firstOrFail());
        }

        if (!$item->is_paid || !$user) {
            return null;
        }

        // Trial takes priority - matches evaluate()'s ordering.
        if ($this->trialService->hasAvailableTrial($user, $item)) {
            $usage = TrialUsage::where('user_id', $user->id)
                ->where('trialable_type', get_class($item))
                ->where('trialable_id', $item->id)
                ->first();

            if (($item->trial_type ?? null) === 'attempts') {
                $used = (int) ($usage->attempts_used ?? 0);
                $total = (int) $item->trial_value;
                return [
                    'kind' => 'trial_attempts',
                    'attempts_used' => $used,
                    'attempts_total' => $total,
                    'attempts_remaining' => max(0, $total - $used),
                ];
            }

            if (($item->trial_type ?? null) === 'days') {
                $expiresAt = ($usage && $usage->first_used_at)
                    ? $usage->first_used_at->copy()->addDays((int) $item->trial_value)
                    : now()->copy()->addDays((int) $item->trial_value);
                return [
                    'kind' => 'trial_days',
                    'expires_at' => $expiresAt->toIso8601String(),
                    'days_remaining' => max(0, (int) ceil(($expiresAt->getTimestamp() - now()->getTimestamp()) / 86400)),
                ];
            }
        }

        $activePurchase = $item instanceof QuestionSetPackage
            ? Purchase::activePackagePurchase($user->id, $item->id)
            : Purchase::activeQuestionSetPurchase($user->id, $item->id);

        if ($activePurchase) {
            if ($activePurchase->expires_at) {
                return [
                    'kind' => 'days',
                    'expires_at' => $activePurchase->expires_at->toIso8601String(),
                    'days_remaining' => max(0, (int) ceil(($activePurchase->expires_at->getTimestamp() - now()->getTimestamp()) / 86400)),
                ];
            }

            if ($activePurchase->access_type === 'attempts') {
                $used = (int) $activePurchase->attempts_used;
                $total = (int) $activePurchase->access_value;
                return [
                    'kind' => 'attempts',
                    'attempts_used' => $used,
                    'attempts_total' => $total,
                    'attempts_remaining' => max(0, $total - $used),
                ];
            }

            return ['kind' => 'unlimited']; // legacy permanent purchase
        }

        if (UserSubscription::where('user_id', $user->id)->active()->exists()) {
            return ['kind' => 'subscription'];
        }

        $balance = (int) $this->walletService->balance($user->id);
        if ($balance > 0) {
            return ['kind' => 'wallet', 'attempts_remaining' => $balance];
        }

        return null;
    }

    /**
     * Priority: free -> trial -> direct ownership -> active subscription -> attempt
     * wallet -> denied (with a paywall payload describing how to buy access).
     */
    private function evaluate(?User $user, QuestionSet|QuestionSetPackage $item, bool $consume): array
    {
        // A question set that belongs to a package is gated entirely by the package -
        // its own is_paid/price fields are unused once it's package-exclusive.
        if ($item instanceof QuestionSet && $item->package_id) {
            return $this->evaluate($user, $item->package ?? $item->package()->firstOrFail(), $consume);
        }

        if (!$item->is_paid) {
            return ['allowed' => true, 'reason' => 'free'];
        }

        if (!$user) {
            return [
                'allowed' => false,
                'reason' => 'auth_required',
                'paywall' => $this->paywallPayload($item),
            ];
        }

        if ($this->trialService->hasAvailableTrial($user, $item)) {
            if ($consume) {
                $this->trialService->consumeTrial($user, $item);
            }
            return ['allowed' => true, 'reason' => 'trial'];
        }

        $activePurchase = $item instanceof QuestionSetPackage
            ? Purchase::activePackagePurchase($user->id, $item->id)
            : Purchase::activeQuestionSetPurchase($user->id, $item->id);

        if ($activePurchase) {
            if ($consume && $activePurchase->access_type === 'attempts') {
                $activePurchase->increment('attempts_used');
            }
            return ['allowed' => true, 'reason' => 'owned'];
        }

        if (UserSubscription::where('user_id', $user->id)->active()->exists()) {
            return ['allowed' => true, 'reason' => 'subscription'];
        }

        if ($this->walletService->balance($user->id) > 0) {
            if ($consume) {
                $this->walletService->debit($user, 1, 'consume', $item);
            }
            return ['allowed' => true, 'reason' => $consume ? 'wallet' : 'wallet_available'];
        }

        return [
            'allowed' => false,
            'reason' => 'purchase_required',
            'paywall' => $this->paywallPayload($item),
        ];
    }

    public function paywallPayload(QuestionSet|QuestionSetPackage $item): array
    {
        $priceTier = $item->priceTier;

        return [
            'price_tier' => $priceTier ? [
                'tier_key' => $priceTier->tier_key,
                'amount' => (float) $priceTier->amount,
                'currency' => $priceTier->currency,
                'ios_product_id' => $priceTier->iosProductId(),
                'android_product_id' => $priceTier->androidProductId(),
                'access_type' => $item->access_type,
                'access_value' => $item->access_value,
            ] : null,
            'attempt_packs' => AttemptPack::where('is_active', true)
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
                ])->values(),
            'subscription_plans' => SubscriptionPlan::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn (SubscriptionPlan $plan) => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'duration_days' => $plan->duration_days,
                    'price' => (float) $plan->price,
                    'currency' => $plan->currency,
                    'product_key' => $plan->product_key,
                    'ios_product_id' => $plan->iosProductId(),
                    'android_product_id' => $plan->androidProductId(),
                ])->values(),
        ];
    }
}
