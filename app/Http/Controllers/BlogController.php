<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Blog::query();

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $blogs = $query->orderBy('created_at', 'desc')->get();

        // Get unique categories
        $categories = Blog::distinct()->pluck('category');

        return view('blogs.index', compact('blogs', 'categories'));
    }

    public function create()
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Blog')) {
            return redirect()->route('blogs.index')->with('error', 'You do not have permission to create blogs.');
        }

        return view('blogs.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Blog')) {
            return redirect()->route('blogs.index')->with('error', 'You do not have permission to create blogs.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'cover_url' => 'nullable|url',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'category' => 'required|string|max:100',
            'author' => 'required|string|max:100',
            'read_time' => 'required|string|max:20',
        ]);

        // Generate slug
        $validated['slug'] = Str::slug($validated['title']);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('blogs', 'public');
            $validated['image'] = $imagePath;
        }

        // Handle published_at
        $validated['published_at'] = now();
        $validated['is_active'] = $request->has('is_active');

        Blog::create($validated);

        return redirect()->route('blogs.index')->with('success', 'Blog created successfully!');
    }

    public function edit(string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Blog')) {
            return redirect()->route('blogs.index')->with('error', 'You do not have permission to edit blogs.');
        }

        $blog = Blog::findOrFail($id);

        return view('blogs.edit', compact('blog'));
    }

    public function update(Request $request, string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Blog')) {
            return redirect()->route('blogs.index')->with('error', 'You do not have permission to update blogs.');
        }

        $blog = Blog::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'cover_url' => 'nullable|url',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'category' => 'required|string|max:100',
            'author' => 'required|string|max:100',
            'read_time' => 'required|string|max:20',
        ]);

        // Update slug if title changed
        if ($validated['title'] !== $blog->title) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($blog->image && \Storage::disk('public')->exists($blog->image)) {
                \Storage::disk('public')->delete($blog->image);
            }

            $imagePath = $request->file('image')->store('blogs', 'public');
            $validated['image'] = $imagePath;
        }

        $validated['is_active'] = $request->has('is_active');

        $blog->update($validated);

        return redirect()->route('blogs.index')->with('success', 'Blog updated successfully!');
    }

    public function destroy(string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete', 'Blog')) {
            return redirect()->route('blogs.index')->with('error', 'You do not have permission to delete blogs.');
        }

        $blog = Blog::findOrFail($id);

        // Delete image if exists
        if ($blog->image && \Storage::disk('public')->exists($blog->image)) {
            \Storage::disk('public')->delete($blog->image);
        }

        $blog->delete();

        return redirect()->route('blogs.index')->with('success', 'Blog deleted successfully!');
    }
}
