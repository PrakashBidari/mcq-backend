<?php

namespace App\Services;

use App\Models\TrialUsage;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TrialService
{
    public function hasAvailableTrial(User $user, Model $item): bool
    {
        if (!$item->trial_enabled || !$item->trial_type) {
            return false;
        }

        $usage = $this->findUsage($user, $item);

        if ($item->trial_type === 'attempts') {
            $used = $usage->attempts_used ?? 0;
            return $used < $item->trial_value;
        }

        if ($item->trial_type === 'days') {
            if (!$usage || !$usage->first_used_at) {
                return true;
            }
            return $usage->first_used_at->diffInDays(now()) < $item->trial_value;
        }

        return false;
    }

    public function consumeTrial(User $user, Model $item): void
    {
        $usage = TrialUsage::firstOrNew([
            'user_id' => $user->id,
            'trialable_type' => get_class($item),
            'trialable_id' => $item->id,
        ]);

        if (!$usage->exists) {
            $usage->first_used_at = now();
            $usage->attempts_used = 0;
        }

        if ($item->trial_type === 'attempts') {
            $usage->attempts_used = ($usage->attempts_used ?? 0) + 1;
        }

        $usage->save();
    }

    private function findUsage(User $user, Model $item): ?TrialUsage
    {
        return TrialUsage::where('user_id', $user->id)
            ->where('trialable_type', get_class($item))
            ->where('trialable_id', $item->id)
            ->first();
    }
}
