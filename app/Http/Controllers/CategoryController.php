<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->withCount(['questionSets', 'children'])->orderBy('parent_id')->orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Category')) {
            return redirect()->route('categories.index')->with('error', 'You do not have permission to create categories.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
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
        $parentCategories = Category::whereNull('parent_id')->where('id', '!=', $id)->orderBy('name')->get();

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
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'slug' => 'required|string|max:255|unique:categories,slug,' . $id,
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $id,
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
}
