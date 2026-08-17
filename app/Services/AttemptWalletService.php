<?php

namespace App\Services;

use App\Models\AttemptWalletTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttemptWalletService
{
    public function balance(int $userId): int
    {
        return AttemptWalletTransaction::latestBalance($userId);
    }

    public function credit(User $user, int $amount, string $reason, ?Model $reference = null): AttemptWalletTransaction
    {
        return $this->applyDelta($user, abs($amount), $reason, $reference);
    }

    /**
     * @throws ValidationException if the debit would push the balance negative
     */
    public function debit(User $user, int $amount, string $reason, ?Model $reference = null): AttemptWalletTransaction
    {
        return $this->applyDelta($user, -abs($amount), $reason, $reference);
    }

    private function applyDelta(User $user, int $delta, string $reason, ?Model $reference): AttemptWalletTransaction
    {
        return DB::transaction(function () use ($user, $delta, $reason, $reference) {
            // Lock the user row itself as the serialization point. A brand-new wallet has
            // no ledger rows yet, so locking the ledger table alone wouldn't prevent two
            // concurrent requests both reading balance=0 and both succeeding.
            DB::table('users')->where('id', $user->id)->lockForUpdate()->first();

            $currentBalance = AttemptWalletTransaction::latestBalance($user->id);
            $newBalance = $currentBalance + $delta;

            if ($newBalance < 0) {
                throw ValidationException::withMessages([
                    'wallet' => 'Insufficient attempt balance.',
                ]);
            }

            return AttemptWalletTransaction::create([
                'user_id' => $user->id,
                'delta' => $delta,
                'reason' => $reason,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'balance_after' => $newBalance,
            ]);
        });
    }
}
