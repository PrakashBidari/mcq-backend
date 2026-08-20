<?php

namespace App\Http\Controllers;

use App\Models\AppPage;
use Illuminate\Http\Request;

class AppPageController extends Controller
{
    // These are fixed, seeded singleton rows (about-app / privacy-policy / study-library)
    // — edit-only, no create/store/destroy, same convention as AdvertisementController.

    public function edit(string $slug)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'AppContent')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to edit this page.');
        }

        $page = AppPage::where('slug', $slug)->firstOrFail();

        return view('app-pages.edit', compact('page'));
    }

    public function update(Request $request, string $slug)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'AppContent')) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to edit this page.');
        }

        $page = AppPage::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'tagline'             => 'nullable|string|max:255',
            'last_updated_label'  => 'nullable|string|max:255',
            'intro_text_1'        => 'nullable|string',
            'intro_text_2'        => 'nullable|string',
            'button_text'         => 'nullable|string|max:50',
            'stat_1_value'        => 'nullable|string|max:50',
            'stat_2_value'        => 'nullable|string|max:50',
            'stat_3_value'        => 'nullable|string|max:50',
            'stat_4_value'        => 'nullable|string|max:50',
            'developer_name'      => 'nullable|string|max:255',
            'developer_role'      => 'nullable|string|max:255',
            'developer_url'       => 'nullable|string|max:255',
            'copyright_text'      => 'nullable|string|max:255',
            'items'               => 'nullable|array',
            'items.*.title'       => 'nullable|string|max:255',
            'items.*.content'     => 'nullable|string',
            'items.*.icon'        => 'nullable|string|max:50',
        ]);

        // Drop rows the admin left completely empty.
        $validated['items'] = collect($validated['items'] ?? [])
            ->filter(fn ($item) => filled($item['title'] ?? null) || filled($item['content'] ?? null))
            ->map(fn ($item) => [
                'title'   => $item['title'] ?? '',
                'content' => $item['content'] ?? '',
                'icon'    => $item['icon'] ?? 'star',
            ])
            ->values()
            ->all();

        $page->update($validated);

        return redirect()->route('app-pages.edit', $slug)->with('success', 'Page updated successfully!');
    }
}
