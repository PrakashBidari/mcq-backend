<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $subscriptionPlans = SubscriptionPlan::orderBy('sort_order')->get();

        return view('subscription-plans.index', compact('subscriptionPlans'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'SubscriptionPlan')) {
            return redirect()->route('subscription-plans.index')->with('error', 'You do not have permission to create subscription plans.');
        }

        return view('subscription-plans.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'SubscriptionPlan')) {
            return redirect()->route('subscription-plans.index')->with('error', 'You do not have permission to create subscription plans.');
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'product_key'   => 'required|string|max:255|unique:subscription_plans,product_key|regex:/^[a-z0-9_]+$/',
            'duration_days' => 'nullable|integer|min:1',
            'is_lifetime'   => 'boolean',
            'price'         => 'required|numeric|min:0.01',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['currency']  = 'JPY';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['duration_days'] = $request->has('is_lifetime') ? null : $validated['duration_days'];
        unset($validated['is_lifetime']);

        SubscriptionPlan::create($validated);

        return redirect()->route('subscription-plans.index')->with('success', 'Subscription plan created! Remember to also create the matching product in App Store Connect / Google Play Console before it can be purchased.');
    }

    public function edit(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'SubscriptionPlan')) {
            return redirect()->route('subscription-plans.index')->with('error', 'You do not have permission to edit subscription plans.');
        }

        $subscriptionPlan = SubscriptionPlan::findOrFail($id);

        return view('subscription-plans.edit', compact('subscriptionPlan'));
    }

    public function update(Request $request, string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'SubscriptionPlan')) {
            return redirect()->route('subscription-plans.index')->with('error', 'You do not have permission to update subscription plans.');
        }

        $subscriptionPlan = SubscriptionPlan::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'duration_days' => 'nullable|integer|min:1',
            'is_lifetime'   => 'boolean',
            'price'         => 'required|numeric|min:0.01',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['duration_days'] = $request->has('is_lifetime') ? null : $validated['duration_days'];
        unset($validated['is_lifetime']);

        $subscriptionPlan->update($validated);

        return redirect()->route('subscription-plans.index')->with('success', 'Subscription plan updated successfully!');
    }

    public function destroy(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete', 'SubscriptionPlan')) {
            return redirect()->route('subscription-plans.index')->with('error', 'You do not have permission to delete subscription plans.');
        }

        $subscriptionPlan = SubscriptionPlan::findOrFail($id);

        if ($subscriptionPlan->purchases()->exists()) {
            return redirect()->route('subscription-plans.index')->with('error', 'This subscription plan has already been purchased and cannot be deleted. Deactivate it instead.');
        }

        $subscriptionPlan->delete();

        return redirect()->route('subscription-plans.index')->with('success', 'Subscription plan deleted successfully!');
    }
}
