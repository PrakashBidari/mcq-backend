<?php

namespace App\Http\Controllers;

use App\Models\QuestionSet;
use App\Models\Category;
use Illuminate\Http\Request;

class QuestionSetController extends Controller
{
    public function index()
    {
        $questionSets = QuestionSet::with('category')->withCount('questions')->get();
        return view('question-sets.index', compact('questionSets'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'QuestionSet')) {
            return redirect()->route('question-sets.index')->with('error', 'No permission.');
        }
        $categories = Category::all();
        return view('question-sets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'QuestionSet')) {
            return redirect()->route('question-sets.index')->with('error', 'No permission.');
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'is_paid'     => 'boolean',
            'price_tier'  => 'nullable|string|in:' . implode(',', array_keys(config('price_tiers.tiers'))) . '|required_if:is_paid,1',
            'time_limit'  => 'nullable|numeric|min:0.1|max:180',
        ]);

        $validated['is_active']  = $request->has('is_active');
        $validated['is_paid']    = $request->has('is_paid');
        $validated['price_tier'] = $validated['is_paid'] ? $request->price_tier : null;
        $validated['price']      = $validated['is_paid'] ? config('price_tiers.tiers')[$request->price_tier] : null;
        $validated['time_limit'] = $request->time_limit ?: null;

        QuestionSet::create($validated);
        return redirect()->route('question-sets.index')->with('success', 'Question set created!');
    }

    public function edit(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'QuestionSet')) {
            return redirect()->route('question-sets.index')->with('error', 'No permission.');
        }
        $questionSet = QuestionSet::withCount('questions')->findOrFail($id);
        $categories  = Category::all();
        return view('question-sets.edit', compact('questionSet', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'QuestionSet')) {
            return redirect()->route('question-sets.index')->with('error', 'No permission.');
        }

        $questionSet = QuestionSet::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'is_paid'     => 'boolean',
            'price_tier'  => 'nullable|string|in:' . implode(',', array_keys(config('price_tiers.tiers'))) . '|required_if:is_paid,1',
            'time_limit'  => 'nullable|numeric|min:0.1|max:180',
        ]);

        $validated['is_active']  = $request->has('is_active');
        $validated['is_paid']    = $request->has('is_paid');
        $validated['price_tier'] = $validated['is_paid'] ? $request->price_tier : null;
        $validated['price']      = $validated['is_paid'] ? config('price_tiers.tiers')[$request->price_tier] : null;
        $validated['time_limit'] = $request->time_limit ?: null;

        $questionSet->update($validated);
        return redirect()->route('question-sets.index')->with('success', 'Question set updated!');
    }

    public function destroy(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete', 'QuestionSet')) {
            return redirect()->route('question-sets.index')->with('error', 'No permission.');
        }
        QuestionSet::findOrFail($id)->delete();
        return redirect()->route('question-sets.index')->with('success', 'Question set deleted!');
    }
}
