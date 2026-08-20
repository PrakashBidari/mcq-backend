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

        $purchases = $query->orderBy('purchased_at', 'desc')->paginate(50);

        return view('purchases.index', compact('purchases'));
    }

    public function subscribers()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('read', 'Purchase')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view subscribers.');
        }

        $activeSubscriptions = UserSubscription::with(['user', 'plan'])->active()->get();
        $activeUserIds = $activeSubscriptions->pluck('user_id')->unique();

        $totalUsers = User::where('role', 'user')->count();
        $paidUsersCount = Purchase::where('status', 'completed')->distinct('user_id')->count('user_id');

        return view('subscribers.index', [
            'activeSubscriptions' => $activeSubscriptions,
            'totalUsers'          => $totalUsers,
            'activeSubscriberCount' => $activeUserIds->count(),
            'paidUsersCount'      => $paidUsersCount,
            'freeUsersCount'      => max(0, $totalUsers - $paidUsersCount),
        ]);
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
            'access_value'    => $target->access_value,
            'expires_at'      => $target->access_type === 'days'
                ? now()->addDays($target->access_value)
                : null,
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
