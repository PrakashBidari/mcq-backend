<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Category;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::with('category');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $faqs = $query->orderBy('order', 'asc')
                     ->orderBy('created_at', 'desc')
                     ->get();

        $categories = Category::all();

        return view('faqs.index', compact('faqs', 'categories'));
    }

    public function create()
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Faq')) {
            return redirect()->route('faqs.index')->with('error', 'You do not have permission to create FAQs.');
        }

        $categories = Category::all();
        return view('faqs.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Faq')) {
            return redirect()->route('faqs.index')->with('error', 'You do not have permission to create FAQs.');
        }

        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        Faq::create($validated);

        return redirect()->route('faqs.index')->with('success', 'FAQ created successfully!');
    }

    public function edit(string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Faq')) {
            return redirect()->route('faqs.index')->with('error', 'You do not have permission to edit FAQs.');
        }

        $faq = Faq::findOrFail($id);
        $categories = Category::all();

        return view('faqs.edit', compact('faq', 'categories'));
    }

    public function update(Request $request, string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Faq')) {
            return redirect()->route('faqs.index')->with('error', 'You do not have permission to update FAQs.');
        }

        $faq = Faq::findOrFail($id);

        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        $faq->update($validated);

        return redirect()->route('faqs.index')->with('success', 'FAQ updated successfully!');
    }

    public function destroy(string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete', 'Faq')) {
            return redirect()->route('faqs.index')->with('error', 'You do not have permission to delete FAQs.');
        }

        $faq = Faq::findOrFail($id);
        $faq->delete();

        return redirect()->route('faqs.index')->with('success', 'FAQ deleted successfully!');
    }
}
