<?php

namespace App\Http\Controllers;

use App\Models\AttemptPack;
use Illuminate\Http\Request;

class AttemptPackController extends Controller
{
    public function index()
    {
        $attemptPacks = AttemptPack::orderBy('sort_order')->get();

        return view('attempt-packs.index', compact('attemptPacks'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'AttemptPack')) {
            return redirect()->route('attempt-packs.index')->with('error', 'You do not have permission to create attempt packs.');
        }

        return view('attempt-packs.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'AttemptPack')) {
            return redirect()->route('attempt-packs.index')->with('error', 'You do not have permission to create attempt packs.');
        }

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'product_key'    => 'required|string|max:255|unique:attempt_packs,product_key|regex:/^[a-z0-9_]+$/',
            'attempts_count' => 'required|integer|min:1',
            'price'          => 'required|numeric|min:0.01',
            'sort_order'     => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['currency']  = 'JPY';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        AttemptPack::create($validated);

        return redirect()->route('attempt-packs.index')->with('success', 'Attempt pack created! Remember to also create the matching product in App Store Connect / Google Play Console before it can be purchased.');
    }

    public function edit(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'AttemptPack')) {
            return redirect()->route('attempt-packs.index')->with('error', 'You do not have permission to edit attempt packs.');
        }

        $attemptPack = AttemptPack::findOrFail($id);

        return view('attempt-packs.edit', compact('attemptPack'));
    }

    public function update(Request $request, string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'AttemptPack')) {
            return redirect()->route('attempt-packs.index')->with('error', 'You do not have permission to update attempt packs.');
        }

        $attemptPack = AttemptPack::findOrFail($id);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'attempts_count' => 'required|integer|min:1',
            'price'          => 'required|numeric|min:0.01',
            'sort_order'     => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $attemptPack->update($validated);

        return redirect()->route('attempt-packs.index')->with('success', 'Attempt pack updated successfully!');
    }

    public function destroy(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete', 'AttemptPack')) {
            return redirect()->route('attempt-packs.index')->with('error', 'You do not have permission to delete attempt packs.');
        }

        $attemptPack = AttemptPack::findOrFail($id);

        if ($attemptPack->purchases()->exists()) {
            return redirect()->route('attempt-packs.index')->with('error', 'This attempt pack has already been purchased and cannot be deleted. Deactivate it instead.');
        }

        $attemptPack->delete();

        return redirect()->route('attempt-packs.index')->with('success', 'Attempt pack deleted successfully!');
    }
}
