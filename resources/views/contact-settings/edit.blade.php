@extends('layouts.dashboard')

@section('title', 'Edit Contact Settings')
@section('page-title', 'Edit Contact Settings')

@section('content')
    <div class="max-w-4xl">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('contact-settings.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Contact Settings
            </a>
        </div>

        <!-- Form Card -->
        <div class="rounded-lg bg-white shadow-sm">
            <div class="border-b border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-800">Edit Contact Settings</h3>
                <p class="mt-1 text-sm text-gray-600">Update contact page information</p>
            </div>

            <form action="{{ route('contact-settings.update', $setting->id) }}" method="POST" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- Hero Section -->
                <div>
                    <h5 class="mb-4 text-lg font-bold text-gray-800">Hero Section</h5>

                    <div class="space-y-4">
                        <div>
                            <label for="hero_title" class="mb-2 block text-sm font-semibold text-gray-700">
                                Hero Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="hero_title" id="hero_title" value="{{ old('hero_title', $setting->hero_title) }}" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                placeholder="Get in Touch">
                            @error('hero_title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="hero_subtitle" class="mb-2 block text-sm font-semibold text-gray-700">
                                Hero Subtitle <span class="text-red-500">*</span>
                            </label>
                            <textarea name="hero_subtitle" id="hero_subtitle" rows="3" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                placeholder="We'd love to hear from you!">{{ old('hero_subtitle', $setting->hero_subtitle) }}</textarea>
                            @error('hero_subtitle')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200"></div>

                <!-- Contact Information -->
                <div>
                    <h5 class="mb-4 text-lg font-bold text-gray-800">Contact Information</h5>

                    <div class="space-y-4">
                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold text-gray-700">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email', $setting->email) }}" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                placeholder="support@example.com">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="mb-2 block text-sm font-semibold text-gray-700">
                                Phone <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $setting->phone) }}" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                placeholder="+977 9876543210">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="address" class="mb-2 block text-sm font-semibold text-gray-700">
                                Address <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="address" id="address" value="{{ old('address', $setting->address) }}" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                placeholder="Kathmandu, Nepal">
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-200"></div>

                <!-- Form Section -->
                <div>
                    <h5 class="mb-4 text-lg font-bold text-gray-800">Form Section</h5>

                    <div class="space-y-4">
                        <div>
                            <label for="form_title" class="mb-2 block text-sm font-semibold text-gray-700">
                                Form Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="form_title" id="form_title" value="{{ old('form_title', $setting->form_title) }}" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                placeholder="Send us a Message">
                            @error('form_title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="form_subtitle" class="mb-2 block text-sm font-semibold text-gray-700">
                                Form Subtitle <span class="text-red-500">*</span>
                            </label>
                            <textarea name="form_subtitle" id="form_subtitle" rows="2" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                placeholder="Fill out the form below...">{{ old('form_subtitle', $setting->form_subtitle) }}</textarea>
                            @error('form_subtitle')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>


                <!-- Submit Buttons -->
                <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                    <button type="submit" class="rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-700">
                        Update Settings
                    </button>
                    <a href="{{ route('contact-settings.index') }}" class="rounded-lg bg-gray-100 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-200">
                        Cancel
                    </a>
                </div>

            </form>
        </div>

    </div>
@endsection
