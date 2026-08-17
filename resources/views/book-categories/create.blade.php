@extends('layouts.dashboard')

@section('title', 'Create Book Category')
@section('page-title', 'Create Book Category')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('book-categories.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                &larr; Back to Book Categories
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm" x-data="{ name: '{{ old('name') }}', slug: '{{ old('slug') }}' }">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Book Category Information</h3>
            </div>

            <form action="{{ route('book-categories.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" x-model="name"
                        @input="slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')"
                        required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500" placeholder="e.g., Fiction">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-semibold text-gray-700 mb-2">Slug <span class="text-red-500">*</span></label>
                    <input type="text" name="slug" id="slug" x-model="slug" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-purple-500">
                    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="color" class="block text-sm font-semibold text-gray-700 mb-2">Color <span class="text-red-500">*</span></label>
                    <input type="color" name="color" id="color" value="{{ old('color', '#7c3aed') }}" class="h-12 w-20 rounded-lg border border-gray-300 cursor-pointer">
                    @error('color')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div x-data="bookCategoryIconPicker()">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Icon</label>

                    <div class="mb-4 p-4 border-2 border-gray-300 rounded-lg flex items-center gap-4">
                        <div class="w-16 h-16 rounded-lg flex items-center justify-center bg-purple-50">
                            <ion-icon :name="selectedIcon" style="font-size: 32px; color: #7c3aed;"></ion-icon>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-700">Selected Icon</p>
                            <p class="text-xs text-gray-500" x-text="selectedIcon"></p>
                        </div>
                    </div>

                    <input type="hidden" name="icon" x-model="selectedIcon">

                    <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-3 max-h-64 overflow-y-auto p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <template x-for="icon in icons" :key="icon">
                            <button
                                type="button"
                                @click="selectedIcon = icon"
                                class="p-3 rounded-lg border-2 transition hover:border-purple-500"
                                :class="selectedIcon === icon ? 'border-purple-500 bg-purple-50' : 'border-gray-200 bg-white'"
                            >
                                <ion-icon :name="icon" style="font-size: 24px;" :style="`color: ${selectedIcon === icon ? '#7c3aed' : '#6b7280'}`"></ion-icon>
                            </button>
                        </template>
                    </div>
                    @error('icon')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3 pt-6 border-t border-gray-200">
                    <button type="submit" class="px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition">Create Book Category</button>
                    <a href="{{ route('book-categories.index') }}" class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script>
        function bookCategoryIconPicker() {
            return {
                selectedIcon: '{{ old('icon', 'library') }}',
                icons: [
                    'library', 'book', 'book-outline', 'bookmark', 'newspaper',
                    'color-palette', 'code-slash', 'briefcase', 'megaphone', 'camera',
                    'musical-notes', 'bulb', 'fitness', 'restaurant',
                    'airplane', 'car', 'bicycle', 'globe', 'home',
                    'heart', 'star', 'trophy', 'rocket', 'flag',
                    'game-controller', 'headset', 'mic', 'desktop', 'phone-portrait',
                    'cart', 'card', 'cash', 'calculator', 'pie-chart',
                    'bar-chart', 'stats-chart', 'trending-up', 'analytics',
                    'people', 'person', 'school', 'flask',
                    'beaker', 'planet', 'leaf', 'cloud', 'thunderstorm',
                    'sunny', 'moon', 'basketball', 'football', 'baseball',
                    'hammer', 'construct', 'brush', 'color-fill', 'shapes'
                ],
            }
        }
    </script>
    @endpush
@endsection
