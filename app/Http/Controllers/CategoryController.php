<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['questionSets', 'children'])->get();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Category::flatTree();

        return view('categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Category')) {
            return redirect()->route('categories.index')->with('error', 'You do not have permission to create categories.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', $this->uniqueAmongSiblings($request)],
            'slug' => 'required|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Category')) {
            return redirect()->route('categories.index')->with('error', 'You do not have permission to edit categories.');
        }

        $category = Category::withCount('questionSets')->findOrFail($id);
        // A category can't be moved under itself or anything below it
        $parentCategories = Category::flatTree([$category->id]);

        return view('categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Category')) {
            return redirect()->route('categories.index')->with('error', 'You do not have permission to update categories.');
        }

        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', $this->uniqueAmongSiblings($request)->ignore($category->id)],
            'slug' => 'required|string|max:255|unique:categories,slug,' . $id,
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:255',
            'parent_id' => ['nullable', 'exists:categories,id', Rule::notIn($category->selfAndDescendantIds())],
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }

    public function destroy(string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete', 'Category')) {
            return redirect()->route('categories.index')->with('error', 'You do not have permission to delete categories.');
        }

        $category = Category::withCount('children')->findOrFail($id);

        if ($category->children_count > 0) {
            return redirect()->route('categories.index')->with('error', 'Cannot delete a category that has subcategories. Delete or reassign them first.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }

    // The same name may be reused under different parents (e.g. "Grammar" under both
    // N5 and N4), just not twice under the same parent.
    private function uniqueAmongSiblings(Request $request)
    {
        return Rule::unique('categories', 'name')
            ->where(fn ($query) => $query->where('parent_id', $request->input('parent_id')));
    }
}