<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('sort_order')->orderBy('id')->get();

        return view('banners.index', compact('banners'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Banner')) {
            return redirect()->route('banners.index')->with('error', 'You do not have permission to create banners.');
        }

        return view('banners.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'Banner')) {
            return redirect()->route('banners.index')->with('error', 'You do not have permission to create banners.');
        }

        $validated = $this->validateRequest($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('banners', 'public');
        }

        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = ((int) Banner::max('sort_order')) + 1;
        }

        $validated['is_active'] = $request->has('is_active');

        Banner::create($validated);

        return redirect()->route('banners.index')->with('success', 'Banner created successfully!');
    }

    public function edit(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Banner')) {
            return redirect()->route('banners.index')->with('error', 'You do not have permission to edit banners.');
        }

        $banner = Banner::findOrFail($id);

        return view('banners.edit', compact('banner'));
    }

    public function update(Request $request, string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Banner')) {
            return redirect()->route('banners.index')->with('error', 'You do not have permission to update banners.');
        }

        $banner = Banner::findOrFail($id);
        $validated = $this->validateRequest($request);

        if ($request->hasFile('image')) {
            if ($banner->image && \Storage::disk('public')->exists($banner->image)) {
                \Storage::disk('public')->delete($banner->image);
            }
            $validated['image'] = $request->file('image')->store('banners', 'public');
        }

        $validated['sort_order'] = $validated['sort_order'] ?? $banner->sort_order;
        $validated['is_active'] = $request->has('is_active');

        $banner->update($validated);

        return redirect()->route('banners.index')->with('success', 'Banner updated successfully!');
    }

    public function destroy(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('delete', 'Banner')) {
            return redirect()->route('banners.index')->with('error', 'You do not have permission to delete banners.');
        }

        $banner = Banner::findOrFail($id);

        if ($banner->image && \Storage::disk('public')->exists($banner->image)) {
            \Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return redirect()->route('banners.index')->with('success', 'Banner deleted successfully!');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'title'      => 'required|string|max:255',
            'subtitle'   => 'nullable|string|max:255',
            'image'      => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
            'image_url'  => 'nullable|url|max:2048',
            'link_type'  => 'required|in:learning,quiz,none',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }
}
