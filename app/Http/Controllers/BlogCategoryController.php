<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    public function index()
    {
        $categories = BlogCategory::withCount('blogs')->orderBy('name')->get();

        return view('blog-categories.index', compact('categories'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'BlogCategory')) {
            return redirect()->route('blog-categories.index')->with('error', 'You do not have permission to create blog categories.');
        }

        return view('blog-categories.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'BlogCategory')) {
            return redirect()->route('blog-categories.index')->with('error', 'You do not have permission to create blog categories.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name',
            'slug' => 'required|string|max:255|unique:blog_categories,slug',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:255',
        ]);

        BlogCategory::create($validated);

        return redirect()->route('blog-categories.index')->with('success', 'Blog category created successfully!');
    }

    public function edit(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'BlogCategory')) {
            return redirect()->route('blog-categories.index')->with('error', 'You do not have permission to edit blog categories.');
        }

        $category = BlogCategory::findOrFail($id);

        return view('blog-categories.edit', compact('category'));
    }

    public function update(Request $request, string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'BlogCategory')) {
            return redirect()->route('blog-categories.index')->with('error', 'You do not have permission to update blog categories.');
        }

        $category = BlogCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name,' . $id,
            'slug' => 'required|string|max:255|unique:blog_categories,slug,' . $id,
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:255',
        ]);

        $category->update($validated);

        return redirect()->route('blog-categories.index')->with('success', 'Blog category updated successfully!');
    }

    public function destroy(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete', 'BlogCategory')) {
            return redirect()->route('blog-categories.index')->with('error', 'You do not have permission to delete blog categories.');
        }

        $category = BlogCategory::withCount('blogs')->findOrFail($id);

        if ($category->blogs_count > 0) {
            return redirect()->route('blog-categories.index')->with('error', 'Cannot delete a category that has blogs assigned to it.');
        }

        $category->delete();

        return redirect()->route('blog-categories.index')->with('success', 'Blog category deleted successfully!');
    }
}
