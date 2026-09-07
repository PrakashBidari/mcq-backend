<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\QuestionSet;
use App\Models\QuestionSetPackage;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseAdminController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('read', 'Purchase')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view purchases.');
        }

        $query = Purchase::with(['user', 'questionSet', 'package', 'attemptPack', 'subscriptionPlan'])
            ->where('status', 'completed');

        if ($request->filled('type')) {
            $query->where('purchase_type', $request->type);
        }

        // Loaded in full - the DataTable on the page does the paging (6/page),
        // searching and column sorting client-side.
        $purchases = $query->orderBy('purchased_at', 'desc')->get()
            ->each(function (Purchase $p) {
                [$p->access_remaining_label, $p->access_is_active] = $this->accessLabel($p);
            });

        return view('purchases.index', compact('purchases'));
    }

    public function subscribers()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('read', 'Purchase')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view subscribers.');
        }

        // Everyone who has bought a question set or package, with what's left of
        // the grant they paid for. The DataTable on the page pages/filters this.
        $rows = Purchase::with(['user', 'questionSet', 'package'])
            ->where('status', 'completed')
            ->whereIn('purchase_type', ['question_set', 'package'])
            ->orderByDesc('purchased_at')
            ->get()
            ->map(function (Purchase $p) {
                $item = $p->questionSet ?? $p->package;
                [$remaining, $active] = $this->accessLabel($p);

                return (object) [
                    'user_name'  => $p->user->name ?? '—',
                    'user_email' => $p->user->email ?? '—',
                    'type'       => $p->purchase_type === 'package' ? 'Package' : 'Question Set',
                    'item_name'  => $item->name ?? '—',
                    'remaining'  => $remaining,
                    'is_active'  => $active,
                    'taken_at'   => $p->purchased_at,
                    'expires_at' => $p->expires_at,
                ];
            });

        $totalUsers = User::where('role', 'user')->count();
        $paidUsersCount = Purchase::where('status', 'completed')->distinct('user_id')->count('user_id');
        $activeSubscriberCount = UserSubscription::active()->distinct('user_id')->count('user_id');

        return view('subscribers.index', [
            'rows'                  => $rows,
            'totalUsers'            => $totalUsers,
            'activeSubscriberCount' => $activeSubscriberCount,
            'paidUsersCount'        => $paidUsersCount,
            'freeUsersCount'        => max(0, $totalUsers - $paidUsersCount),
        ]);
    }

    /**
     * Human label for how much of a purchase's grant is left, plus whether it's
     * still usable. Returns [string $label, bool $active].
     */
    private function accessLabel(Purchase $p): array
    {
        $active = $p->isActive();

        if ($p->access_type === 'attempts') {
            $total = (int) $p->access_value;
            $left = max(0, $total - (int) $p->attempts_used);
            return ["{$left} / {$total} attempts left", $active];
        }

        if (in_array($p->access_type, ['days', 'hours', 'minutes'], true)) {
            if (!$p->expires_at) {
                return ['—', false];
            }
            if (now()->gte($p->expires_at)) {
                return ['Expired', false];
            }
            $secondsLeft = $p->expires_at->getTimestamp() - now()->getTimestamp();
            [$unit, $count] = match ($p->access_type) {
                'minutes' => ['minute', (int) ceil($secondsLeft / 60)],
                'hours'   => ['hour', (int) ceil($secondsLeft / 3600)],
                default   => ['day', (int) ceil($secondsLeft / 86400)],
            };
            return [$count . ' ' . $unit . ($count === 1 ? '' : 's') . ' left', $active];
        }

        return ['Full access', $active];
    }

    public function revenue(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('read', 'Purchase')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view revenue.');
        }

        $completed = Purchase::where('status', 'completed');

        $totalsByType = (clone $completed)
            ->selectRaw('purchase_type, COUNT(*) as count, SUM(price_paid) as total')
            ->groupBy('purchase_type')
            ->get()
            ->keyBy('purchase_type');

        $monthly = (clone $completed)
            ->selectRaw("DATE_FORMAT(purchased_at, '%Y-%m') as month, SUM(price_paid) as total, COUNT(*) as count")
            ->whereNotNull('purchased_at')
            ->groupBy('month')
            ->orderByDesc('month')
            ->limit(12)
            ->get();

        $grandTotal = (clone $completed)->sum('price_paid');

        return view('revenue.index', compact('totalsByType', 'monthly', 'grandTotal'));
    }

    // Manual recovery lever: for the rare case a real store purchase never made it into
    // the `purchases` table (e.g. a customer emails a receipt screenshot after a
    // verification failure), an admin can grant access directly without touching the DB.
    public function grantForm()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Purchase')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to grant purchases.');
        }

        return view('purchases.grant');
    }

    public function grant(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Purchase')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to grant purchases.');
        }

        $validated = $request->validate([
            'user_email'      => 'required|email|exists:users,email',
            'purchase_type'   => 'required|in:question_set,package',
            'question_set_id' => 'required_if:purchase_type,question_set|exists:question_sets,id',
            'package_id'      => 'required_if:purchase_type,package|exists:question_set_packages,id',
            'reason'          => 'required|string|max:1000',
        ]);

        $user = User::where('email', $validated['user_email'])->firstOrFail();
        $isPackage = $validated['purchase_type'] === 'package';
        $target = $isPackage
            ? QuestionSetPackage::findOrFail($validated['package_id'])
            : QuestionSet::findOrFail($validated['question_set_id']);

        $purchase = Purchase::create([
            'user_id'         => $user->id,
            'purchase_type'   => $validated['purchase_type'],
            $isPackage ? 'package_id' : 'question_set_id' => $target->id,
            'platform'        => 'manual',
            'product_id'      => 'manual_grant',
            'price_tier'      => $target->priceTier?->tier_key ?? 'manual',
            'transaction_id'  => 'manual_' . Str::uuid(),
            'price_paid'      => 0,
            'currency'        => 'JPY',
            'status'          => 'completed',
            'purchased_at'    => now(),
            'access_type'     => $target->access_type,
            'access_value'    => $target->access_type ? max(1, (int) $target->access_value) : null,
            'expires_at'      => match ($target->access_type) {
                'days'    => now()->addDays(max(1, (int) $target->access_value)),
                'hours'   => now()->addHours(max(1, (int) $target->access_value)),
                'minutes' => now()->addMinutes(max(1, (int) $target->access_value)),
                default   => null,
            },
        ]);

        \Log::info('Purchase manually granted by admin', [
            'admin_id'    => auth()->id(),
            'user_id'     => $user->id,
            'purchase_id' => $purchase->id,
            'reason'      => $validated['reason'],
        ]);

        return redirect()->route('purchases.index')->with('success', "Access granted to {$user->email}.");
    }
}
