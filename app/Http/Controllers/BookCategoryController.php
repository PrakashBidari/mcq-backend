<?php

namespace App\Http\Controllers;

use App\Models\BookCategory;
use Illuminate\Http\Request;

class BookCategoryController extends Controller
{
    public function index()
    {
        $categories = BookCategory::withCount('books')->orderBy('name')->get();

        return view('book-categories.index', compact('categories'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'BookCategory')) {
            return redirect()->route('book-categories.index')->with('error', 'You do not have permission to create book categories.');
        }

        return view('book-categories.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'BookCategory')) {
            return redirect()->route('book-categories.index')->with('error', 'You do not have permission to create book categories.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:book_categories,name',
            'slug' => 'required|string|max:255|unique:book_categories,slug',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:255',
        ]);

        BookCategory::create($validated);

        return redirect()->route('book-categories.index')->with('success', 'Book category created successfully!');
    }

    public function edit(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'BookCategory')) {
            return redirect()->route('book-categories.index')->with('error', 'You do not have permission to edit book categories.');
        }

        $category = BookCategory::findOrFail($id);

        return view('book-categories.edit', compact('category'));
    }

    public function update(Request $request, string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'BookCategory')) {
            return redirect()->route('book-categories.index')->with('error', 'You do not have permission to update book categories.');
        }

        $category = BookCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:book_categories,name,' . $id,
            'slug' => 'required|string|max:255|unique:book_categories,slug,' . $id,
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'icon' => 'nullable|string|max:255',
        ]);

        $category->update($validated);

        return redirect()->route('book-categories.index')->with('success', 'Book category updated successfully!');
    }

    public function destroy(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete', 'BookCategory')) {
            return redirect()->route('book-categories.index')->with('error', 'You do not have permission to delete book categories.');
        }

        $category = BookCategory::withCount('books')->findOrFail($id);

        if ($category->books_count > 0) {
            return redirect()->route('book-categories.index')->with('error', 'Cannot delete a category that has books assigned to it.');
        }

        $category->delete();

        return redirect()->route('book-categories.index')->with('success', 'Book category deleted successfully!');
    }
}
