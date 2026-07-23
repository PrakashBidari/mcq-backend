<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    // Advertisements are seeded slots, not user-created records:
    // only index/edit/update/toggleStatus are exposed — no create, store, or destroy.

    public function index()
    {
        $advertisements = Advertisement::orderBy('position', 'asc')->get();

        return view('advertisements.index', compact('advertisements'));
    }

    public function edit(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Advertise')) {
            return redirect()->route('advertisements.index')->with('error', 'You do not have permission to edit advertisements.');
        }

        $advertisement = Advertisement::findOrFail($id);

        return view('advertisements.edit', compact('advertisement'));
    }

    public function update(Request $request, string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Advertise')) {
            return redirect()->route('advertisements.index')->with('error', 'You do not have permission to edit advertisements.');
        }

        $advertisement = Advertisement::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'button_text' => 'required|string|max:50',
            'link_url' => 'nullable|url|max:2048',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $advertisement->update($validated);

        return redirect()->route('advertisements.index')->with('success', 'Advertisement updated successfully!');
    }

    public function toggleStatus(string $id)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'Advertise')) {
            return redirect()->route('advertisements.index')->with('error', 'You do not have permission to update advertisements.');
        }

        $advertisement = Advertisement::findOrFail($id);
        $advertisement->update(['is_active' => !$advertisement->is_active]);

        return redirect()->route('advertisements.index')->with('success', 'Advertisement status updated!');
    }
}
