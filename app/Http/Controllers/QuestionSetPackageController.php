<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PriceTier;
use App\Models\QuestionSet;
use App\Models\QuestionSetPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuestionSetPackageController extends Controller
{
    public function index()
    {
        $packages = QuestionSetPackage::with(['category', 'subcategory'])->withCount('questionSets')->orderBy('name')->get();
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();

        return view('packages.index', compact('packages', 'categories'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Package')) {
            return redirect()->route('packages.index')->with('error', 'You do not have permission to create packages.');
        }

        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        $priceTiers = PriceTier::where('is_active', true)->orderBy('sort_order')->get();

        return view('packages.create', compact('categories', 'priceTiers'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Package')) {
            return redirect()->route('packages.index')->with('error', 'You do not have permission to create packages.');
        }

        $validated = $this->validateRequest($request);
        $questionSetIds = $request->input('question_set_ids', []);

        $package = QuestionSetPackage::create($validated);

        // Question sets are tagged with whichever category they actually live under
        // (the subcategory itself when one exists), not the top-level parent.
        $effectiveCategoryId = $package->subcategory_id ?: $package->category_id;

        if (!empty($questionSetIds)) {
            QuestionSet::whereIn('id', $questionSetIds)
                ->where('category_id', $effectiveCategoryId)
                ->whereNull('package_id')
                ->update(['package_id' => $package->id]);
        }

        return redirect()->route('packages.index')->with('success', 'Package created successfully!');
    }

    public function edit(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Package')) {
            return redirect()->route('packages.index')->with('error', 'You do not have permission to edit packages.');
        }

        $package = QuestionSetPackage::with('questionSets')->findOrFail($id);
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        $priceTiers = PriceTier::where('is_active', true)->orderBy('sort_order')->get();

        // Question sets are tagged with whichever category they actually live under
        // (the subcategory itself when one exists), not the top-level parent.
        $effectiveCategoryId = $package->subcategory_id ?: $package->category_id;

        // Sets already in this package, plus unpackaged sets in its category, are selectable
        $availableSets = QuestionSet::where('category_id', $effectiveCategoryId)
            ->where(function ($query) use ($package) {
                $query->whereNull('package_id')->orWhere('package_id', $package->id);
            })
            ->orderBy('name')
            ->get();

        return view('packages.edit', compact('package', 'categories', 'priceTiers', 'availableSets'));
    }

    public function update(Request $request, string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Package')) {
            return redirect()->route('packages.index')->with('error', 'You do not have permission to update packages.');
        }

        $package = QuestionSetPackage::findOrFail($id);
        $validated = $this->validateRequest($request, $id);
        $questionSetIds = $request->input('question_set_ids', []);

        $package->update($validated);
        $package->refresh();

        // Un-package any sets that were removed from the selection
        QuestionSet::where('package_id', $package->id)
            ->whereNotIn('id', $questionSetIds)
            ->update(['package_id' => null]);

        // Question sets are tagged with whichever category they actually live under
        // (the subcategory itself when one exists), not the top-level parent.
        $effectiveCategoryId = $package->subcategory_id ?: $package->category_id;

        if (!empty($questionSetIds)) {
            QuestionSet::whereIn('id', $questionSetIds)
                ->where('category_id', $effectiveCategoryId)
                ->update(['package_id' => $package->id]);
        }

        return redirect()->route('packages.index')->with('success', 'Package updated successfully!');
    }

    public function destroy(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete', 'Package')) {
            return redirect()->route('packages.index')->with('error', 'You do not have permission to delete packages.');
        }

        $package = QuestionSetPackage::findOrFail($id);

        // Un-package its sets rather than deleting them - a package is a grouping,
        // not the owner of the question sets' content.
        QuestionSet::where('package_id', $package->id)->update(['package_id' => null]);
        $package->delete();

        return redirect()->route('packages.index')->with('success', 'Package deleted successfully! Its question sets were kept and moved back to single browsing.');
    }

    // AJAX: question sets available to add to a package for a given category/subcategory.
    // category_id here should be the effective (leaf) category - the subcategory id when
    // one is selected, otherwise the top-level category id - since that's what's actually
    // stored on question_sets.category_id.
    public function availableQuestionSets(Request $request)
    {
        $categoryId = $request->query('category_id');
        $packageId = $request->query('package_id');

        $sets = QuestionSet::where('category_id', $categoryId)
            ->where(function ($query) use ($packageId) {
                $query->whereNull('package_id');
                if ($packageId) {
                    $query->orWhere('package_id', $packageId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'is_paid']);

        return response()->json(['success' => true, 'data' => $sets]);
    }

    private function validateRequest(Request $request, ?string $id = null): array
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'category_id'    => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
            'is_paid'        => 'boolean',
            'price'          => 'nullable|numeric|min:0.01|required_if:is_paid,1',
            'access_type'    => 'nullable|in:attempts,days|required_if:is_paid,1',
            'access_value'   => 'nullable|integer|min:1|required_if:is_paid,1',
            'trial_enabled'  => 'boolean',
            'trial_type'     => 'nullable|in:attempts,days|required_if:trial_enabled,1',
            'trial_value'    => 'nullable|integer|min:1|required_if:trial_enabled,1',
        ]);

        $validated['slug'] = $id
            ? QuestionSetPackage::find($id)->slug
            : $this->uniqueSlug($validated['name']);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_paid']   = $request->has('is_paid');

        if ($validated['is_paid']) {
            $priceTier = PriceTier::forAmount((float) $validated['price']);
            $validated['price_tier_id'] = $priceTier->id;
        } else {
            $validated['price_tier_id'] = null;
            $validated['access_type']   = null;
            $validated['access_value']  = null;
        }
        unset($validated['price']);

        $validated['trial_enabled'] = $request->has('trial_enabled');
        if (!$validated['trial_enabled']) {
            $validated['trial_type']  = null;
            $validated['trial_value'] = null;
        }

        return $validated;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (QuestionSetPackage::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
