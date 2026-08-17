<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

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
}
