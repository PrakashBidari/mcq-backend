<?php

namespace App\Http\Controllers;

use App\Models\QuestionSet;
use App\Models\QuestionSetPackage;
use App\Models\Category;
use App\Models\PriceTier;
use Illuminate\Http\Request;

class QuestionSetController extends Controller
{
    public function index()
    {
        $questionSets = QuestionSet::with(['category.parent', 'package'])->withCount('questions')->get();
        return view('question-sets.index', compact('questionSets'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'QuestionSet')) {
            return redirect()->route('question-sets.index')->with('error', 'No permission.');
        }
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        $packages = QuestionSetPackage::orderBy('name')->get();
        $priceTiers = PriceTier::where('is_active', true)->orderBy('sort_order')->get();
        [$selectedCategoryId, $selectedSubcategoryId] = $this->splitCategorySelection($categories, old('category_id'));
        return view('question-sets.create', compact('categories', 'packages', 'priceTiers', 'selectedCategoryId', 'selectedSubcategoryId'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'QuestionSet')) {
            return redirect()->route('question-sets.index')->with('error', 'No permission.');
        }

        $validated = $this->validateRequest($request);

        QuestionSet::create($validated);
        return redirect()->route('question-sets.index')->with('success', 'Question set created!');
    }

    public function edit(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'QuestionSet')) {
            return redirect()->route('question-sets.index')->with('error', 'No permission.');
        }
        $questionSet = QuestionSet::withCount('questions')->findOrFail($id);
        $categories  = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        $packages = QuestionSetPackage::orderBy('name')->get();
        $priceTiers = PriceTier::where('is_active', true)->orderBy('sort_order')->get();
        [$selectedCategoryId, $selectedSubcategoryId] = $this->splitCategorySelection($categories, old('category_id', $questionSet->category_id));
        return view('question-sets.edit', compact('questionSet', 'categories', 'packages', 'priceTiers', 'selectedCategoryId', 'selectedSubcategoryId'));
    }

    public function update(Request $request, string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'QuestionSet')) {
            return redirect()->route('question-sets.index')->with('error', 'No permission.');
        }

        $questionSet = QuestionSet::findOrFail($id);
        $validated = $this->validateRequest($request);

        $questionSet->update($validated);
        return redirect()->route('question-sets.index')->with('success', 'Question set updated!');
    }

    // Given a top-level Category collection (each with its `children` loaded) and an
    // effective category id (which may belong to a top-level category or one of its
    // children), returns [categoryId, subcategoryId] so the create/edit forms can
    // preselect the right pair of dropdowns.
    private function splitCategorySelection($categories, $effectiveId): array
    {
        if (!$effectiveId) {
            return [null, null];
        }

        $effectiveId = (int) $effectiveId;

        foreach ($categories as $category) {
            if ($category->id === $effectiveId) {
                return [$category->id, null];
            }

            $child = $category->children->firstWhere('id', $effectiveId);
            if ($child) {
                return [$category->id, $child->id];
            }
        }

        return [null, null];
    }

    private function validateRequest(Request $request): array
    {
        $validated = $request->validate([
            'category_id'   => 'required|exists:categories,id',
            'package_id'    => 'nullable|exists:question_set_packages,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'is_active'     => 'boolean',
            'is_paid'       => 'boolean',
            'price_tier_id' => 'nullable|exists:price_tiers,id|required_if:is_paid,1',
            'access_type'   => 'nullable|in:attempts,days,hours,minutes|required_if:is_paid,1',
            'access_value'  => 'nullable|integer|min:1|required_if:is_paid,1',
            'time_limit'    => 'nullable|numeric|min:0.1|max:180',
            'trial_enabled' => 'boolean',
            'trial_type'    => 'nullable|in:attempts,days|required_if:trial_enabled,1',
            'trial_value'   => 'nullable|integer|min:1|required_if:trial_enabled,1',
        ]);

        $validated['is_active']  = $request->has('is_active');
        $validated['is_paid']    = $request->has('is_paid');
        $validated['package_id'] = $request->package_id ?: null;

        if ($validated['is_paid']) {
            $priceTier = PriceTier::findOrFail($validated['price_tier_id']);
            $validated['price_tier']    = $priceTier->tier_key;
            $validated['price_tier_id'] = $priceTier->id;
            $validated['price']         = $priceTier->amount;
        } else {
            $validated['price_tier']    = null;
            $validated['price_tier_id'] = null;
            $validated['price']         = null;
            $validated['access_type']   = null;
            $validated['access_value']  = null;
        }

        $validated['trial_enabled'] = $request->has('trial_enabled');
        if (!$validated['trial_enabled']) {
            $validated['trial_type']  = null;
            $validated['trial_value'] = null;
        }

        $validated['time_limit'] = $request->time_limit ?: null;

        return $validated;
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
