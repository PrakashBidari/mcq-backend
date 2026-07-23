@extends('layouts.dashboard')

@section('title', 'Edit Advertisement')
@section('page-title', 'Edit Advertisement')

@section('content')
    <div class="max-w-4xl">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('advertisements.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back to Advertisements
            </a>
        </div>

        <!-- Form Card -->
        <div class="rounded-lg bg-white shadow-sm">
            <div class="border-b border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-800">Edit Advertisement — {{ ucfirst($advertisement->position) }} slot</h3>
                <p class="mt-1 text-sm text-gray-600">Update the content shown in this ad slot on the mobile app</p>
            </div>

            <form action="{{ route('advertisements.update', $advertisement->id) }}" method="POST" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- Title -->
                <div>
                    <label for="title" class="mb-2 block text-sm font-semibold text-gray-700">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title', $advertisement->title) }}"
                        required maxlength="255"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="Your Ad Here">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="mb-2 block text-sm font-semibold text-gray-700">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" id="description" rows="4" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="Promote your product to active learners.">{{ old('description', $advertisement->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Button Text -->
                    <div>
                        <label for="button_text" class="mb-2 block text-sm font-semibold text-gray-700">
                            Button Text <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="button_text" id="button_text"
                            value="{{ old('button_text', $advertisement->button_text) }}" required maxlength="50"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="Learn More">
                        @error('button_text')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Link URL -->
                    <div>
                        <label for="link_url" class="mb-2 block text-sm font-semibold text-gray-700">
                            Link URL
                        </label>
                        <input type="url" name="link_url" id="link_url"
                            value="{{ old('link_url', $advertisement->link_url) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="https://example.com">
                        @error('link_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Opened when the button is tapped in the app. Leave blank for no link.</p>
                    </div>
                </div>

                <!-- Active Status -->
                <div class="flex items-center gap-3 rounded-lg bg-gray-50 p-4">
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" name="is_active" id="is_active" class="peer sr-only"
                            {{ old('is_active', $advertisement->is_active) ? 'checked' : '' }}>
                        <div
                            class="h-6 w-11 rounded-full bg-gray-300 transition-colors after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-green-500 peer-checked:after:translate-x-full peer-checked:after:border-white">
                        </div>
                    </label>
                    <label for="is_active" class="text-sm font-semibold text-gray-700">
                        Active (visible in mobile app)
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                    <button type="submit"
                        class="rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-700">
                        Update Advertisement
                    </button>
                    <a href="{{ route('advertisements.index') }}"
                        class="rounded-lg bg-gray-100 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-200">
                        Cancel
                    </a>
                </div>

            </form>
        </div>

    </div>
@endsection
