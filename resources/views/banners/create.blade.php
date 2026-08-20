@extends('layouts.dashboard')

@section('title', 'Create Banner')
@section('page-title', 'Create New Banner')

@section('content')
    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('banners.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Banners
            </a>
        </div>

        <div class="rounded-lg bg-white shadow-sm">
            <div class="border-b border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-800">Banner Information</h3>
                <p class="mt-1 text-sm text-gray-600">Fill in the details for a new home-page hero slide</p>
            </div>

            <form action="{{ route('banners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 p-6">
                @csrf

                <!-- Title -->
                <div>
                    <label for="title" class="mb-2 block text-sm font-semibold text-gray-700">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="e.g. Master Your Skills Today">
                    <p class="mt-1 text-xs text-gray-500">Use \n for a line break, matching the app's title layout.</p>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Subtitle -->
                <div>
                    <label for="subtitle" class="mb-2 block text-sm font-semibold text-gray-700">Subtitle</label>
                    <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="e.g. Learn from world-class instructors">
                    @error('subtitle')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image URL -->
                <div>
                    <label for="image_url" class="mb-2 block text-sm font-semibold text-gray-700">Image URL</label>
                    <input type="url" name="image_url" id="image_url" value="{{ old('image_url') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="https://images.unsplash.com/photo-xxxxx">
                    <p class="mt-1 text-xs text-gray-500">Enter a valid image URL (used if no file is uploaded below).</p>
                    @error('image_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Upload Image -->
                <div>
                    <label for="image" class="mb-2 block text-sm font-semibold text-gray-700">Or Upload Image</label>
                    <input type="file" name="image" id="image" accept="image/*"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                    @error('image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Upload an image file (JPG, PNG, GIF, WebP - max 2MB). Takes priority over the URL above.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Link Type -->
                    <div>
                        <label for="link_type" class="mb-2 block text-sm font-semibold text-gray-700">
                            Tapping the slide opens <span class="text-red-500">*</span>
                        </label>
                        <select name="link_type" id="link_type" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                            <option value="quiz" {{ old('link_type') === 'quiz' ? 'selected' : '' }}>Quiz tab</option>
                            <option value="learning" {{ old('link_type', 'learning') === 'learning' ? 'selected' : '' }}>Study tab</option>
                            <option value="none" {{ old('link_type') === 'none' ? 'selected' : '' }}>Nothing (no button)</option>
                        </select>
                        @error('link_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label for="sort_order" class="mb-2 block text-sm font-semibold text-gray-700">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" min="0" value="{{ old('sort_order') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g. 1">
                        <p class="mt-1 text-xs text-gray-500">Lower numbers show first. Leave empty to add at the end.</p>
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Active Status -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <label for="is_active" class="text-sm font-semibold text-gray-700">Active (visible in the app)</label>
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                    <button type="submit"
                        class="rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-700">
                        Create Banner
                    </button>
                    <a href="{{ route('banners.index') }}"
                        class="rounded-lg bg-gray-100 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-200">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
