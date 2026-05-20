<?php

namespace App\Http\Controllers;

use App\Models\ContactSetting;
use Illuminate\Http\Request;

class ContactSettingController extends Controller
{
    public function index()
    {
        $setting = ContactSetting::first(); // Get the single setting
        return view('contact-settings.index', compact('setting'));
    }

    public function create()
    {
        // Check if setting already exists
        if (ContactSetting::exists()) {
            return redirect()->route('contact-settings.index')
                ->with('error', 'Contact settings already exist. You can only edit the existing settings.');
        }

        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'ContactSetting')) {
            return redirect()->route('contact-settings.index')
                ->with('error', 'You do not have permission to create contact settings.');
        }

        return view('contact-settings.create');
    }

    public function store(Request $request)
    {
        // Check if setting already exists
        if (ContactSetting::exists()) {
            return redirect()->route('contact-settings.index')
                ->with('error', 'Contact settings already exist. You can only edit the existing settings.');
        }

        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('create', 'ContactSetting')) {
            return redirect()->route('contact-settings.index')
                ->with('error', 'You do not have permission to create contact settings.');
        }

        $validated = $request->validate([
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'required|string|max:500',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'form_title' => 'required|string|max:255',
            'form_subtitle' => 'required|string|max:500',
        ]);

        $validated['is_active'] = true; // Always active since there's only one

        ContactSetting::create($validated);

        return redirect()->route('contact-settings.index')
            ->with('success', 'Contact settings created successfully!');
    }

    public function edit(string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'ContactSetting')) {
            return redirect()->route('contact-settings.index')
                ->with('error', 'You do not have permission to edit contact settings.');
        }

        $setting = ContactSetting::findOrFail($id);
        return view('contact-settings.edit', compact('setting'));
    }

    public function update(Request $request, string $id)
    {
        // Check permission
        if (!auth()->user()->isAdmin() && !auth()->user()->hasPermission('update', 'ContactSetting')) {
            return redirect()->route('contact-settings.index')
                ->with('error', 'You do not have permission to update contact settings.');
        }

        $setting = ContactSetting::findOrFail($id);

        $validated = $request->validate([
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'required|string|max:500',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'form_title' => 'required|string|max:255',
            'form_subtitle' => 'required|string|max:500',
        ]);

        $validated['is_active'] = true; // Always active

        $setting->update($validated);

        return redirect()->route('contact-settings.index')
            ->with('success', 'Contact settings updated successfully!');
    }

    public function destroy(string $id)
    {
        return redirect()->route('contact-settings.index')
            ->with('error', 'Cannot delete contact settings. You can only edit them.');
    }
}
