<?php

namespace App\Http\Controllers;

use App\Models\PriceTier;
use Illuminate\Http\Request;

class PriceTierController extends Controller
{
    public function index()
    {
        $priceTiers = PriceTier::orderBy('sort_order')->get();

        return view('price-tiers.index', compact('priceTiers'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'PriceTier')) {
            return redirect()->route('price-tiers.index')->with('error', 'You do not have permission to create price tiers.');
        }

        return view('price-tiers.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'PriceTier')) {
            return redirect()->route('price-tiers.index')->with('error', 'You do not have permission to create price tiers.');
        }

        $validated = $request->validate([
            'tier_key'   => 'required|string|max:255|unique:price_tiers,tier_key|regex:/^[a-z0-9_]+$/',
            'label'      => 'nullable|string|max:255',
            'amount'     => 'required|numeric|min:0.01',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['currency']  = 'JPY';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        PriceTier::create($validated);

        return redirect()->route('price-tiers.index')->with('success', 'Price tier created! Remember to also create the matching product in App Store Connect / Google Play Console before it can be purchased.');
    }

    public function edit(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'PriceTier')) {
            return redirect()->route('price-tiers.index')->with('error', 'You do not have permission to edit price tiers.');
        }

        $priceTier = PriceTier::findOrFail($id);

        return view('price-tiers.edit', compact('priceTier'));
    }

    public function update(Request $request, string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'PriceTier')) {
            return redirect()->route('price-tiers.index')->with('error', 'You do not have permission to update price tiers.');
        }

        $priceTier = PriceTier::findOrFail($id);

        $validated = $request->validate([
            'label'      => 'nullable|string|max:255',
            'amount'     => 'required|numeric|min:0.01',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $priceTier->update($validated);

        return redirect()->route('price-tiers.index')->with('success', 'Price tier updated successfully!');
    }

    public function toggle(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'PriceTier')) {
            return redirect()->route('price-tiers.index')->with('error', 'You do not have permission to update price tiers.');
        }

        $priceTier = PriceTier::findOrFail($id);
        $priceTier->update(['is_active' => !$priceTier->is_active]);

        return redirect()->route('price-tiers.index')->with('success', 'Price tier status updated!');
    }
}
