<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('category');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $books = $query->get();
        $categories = Category::all();

        return view('books.index', compact('books', 'categories'));
    }

    public function create()
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Book')) {
            return redirect()->route('books.index')->with('error', 'You do not have permission to create books.');
        }

        $categories = Category::all();
        return view('books.create', compact('categories'));
    }



    public function edit(string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Book')) {
            return redirect()->route('books.index')->with('error', 'You do not have permission to edit books.');
        }

        $book = Book::findOrFail($id);
        $categories = Category::all();

        return view('books.edit', compact('book', 'categories'));
    }


    public function store(Request $request)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Book')) {
            return redirect()->route('books.index')->with('error', 'You do not have permission to create books.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string', // ← Add this
            'cover' => 'required|url',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'rating' => 'required|numeric|min:0|max:5',
            'pages' => 'required|integer|min:1',
            'duration' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'difficulty' => 'required|in:Beginner,Intermediate,Advanced',
            'students' => 'required|integer|min:0',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('books', 'public');
            $validated['image'] = $imagePath;
        }

        // Add is_active
        $validated['is_active'] = $request->has('is_active');

        Book::create($validated);

        return redirect()->route('books.index')->with('success', 'Book created successfully!');
    }

    public function update(Request $request, string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Book')) {
            return redirect()->route('books.index')->with('error', 'You do not have permission to update books.');
        }

        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string', // ← Add this
            'cover' => 'required|url',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'rating' => 'required|numeric|min:0|max:5',
            'pages' => 'required|integer|min:1',
            'duration' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'difficulty' => 'required|in:Beginner,Intermediate,Advanced',
            'students' => 'required|integer|min:0',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($book->image && \Storage::disk('public')->exists($book->image)) {
                \Storage::disk('public')->delete($book->image);
            }

            $imagePath = $request->file('image')->store('books', 'public');
            $validated['image'] = $imagePath;
        }

        // Add is_active
        $validated['is_active'] = $request->has('is_active');

        $book->update($validated);

        return redirect()->route('books.index')->with('success', 'Book updated successfully!');
    }

    public function destroy(string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete', 'Book')) {
            return redirect()->route('books.index')->with('error', 'You do not have permission to delete books.');
        }

        $book = Book::findOrFail($id);

        // Delete image if exists
        if ($book->image && \Storage::disk('public')->exists($book->image)) {
            \Storage::disk('public')->delete($book->image);
        }

        $book->delete();

        return redirect()->route('books.index')->with('success', 'Book deleted successfully!');
    }
}
