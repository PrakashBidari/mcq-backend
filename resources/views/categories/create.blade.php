@extends('layouts.dashboard')

@section('title', 'Create Category')
@section('page-title', 'Create New Category')

@section('content')
    <div class="max-w-3xl">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('categories.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Categories
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-sm" x-data="categoryForm()">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Category Information</h3>
                <p class="text-sm text-gray-600 mt-1">Fill in the details to create a new category</p>
            </div>

            <form action="{{ route('categories.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Category Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        x-model="name"
                        @input="generateSlug()"
                        value="{{ old('name') }}"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="e.g., Design, Development, Business"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug (Auto-generated) -->
                <div>
                    <label for="slug" class="block text-sm font-semibold text-gray-700 mb-2">
                        Slug <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="slug"
                        id="slug"
                        x-model="slug"
                        value="{{ old('slug') }}"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="auto-generated-slug"
                    >
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Auto-generated from name. You can edit it if needed.</p>
                </div>

                <!-- Parent Category -->
                <div>
                    <label for="parent_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        Parent Category
                    </label>
                    <select name="parent_id" id="parent_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">None — this is a top-level category (e.g. SSW, JLPT)</option>
                        @foreach ($parentCategories as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Leave empty for a top-level category. Choose a parent to make this a subcategory (e.g. Hotel under SSW).</p>
                    @error('parent_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="Brief description of this category..."
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Color Picker -->
                <div>
                    <label for="color" class="block text-sm font-semibold text-gray-700 mb-2">
                        Category Color <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-4">
                        <input
                            type="color"
                            name="color"
                            id="color"
                            x-model="color"
                            value="{{ old('color', '#7c3aed') }}"
                            class="h-12 w-20 rounded-lg border border-gray-300 cursor-pointer"
                        >
                        <input
                            type="text"
                            x-model="color"
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg bg-gray-50"
                            placeholder="#7c3aed"
                            readonly
                        >
                        <div class="w-12 h-12 rounded-lg border border-gray-300" :style="`background-color: ${color}`"></div>
                    </div>
                    @error('color')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Icon Picker -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Category Icon <span class="text-red-500">*</span>
                    </label>

                    <!-- Selected Icon Display -->
                    <div class="mb-4 p-4 border-2 border-gray-300 rounded-lg flex items-center gap-4">
                        <div class="w-16 h-16 rounded-lg flex items-center justify-center" :style="`background-color: ${color}15`">
                            <ion-icon :name="selectedIcon" :style="`font-size: 32px; color: ${color}`"></ion-icon>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-700">Selected Icon</p>
                            <p class="text-xs text-gray-500" x-text="selectedIcon"></p>
                        </div>
                    </div>

                    <input type="hidden" name="icon" x-model="selectedIcon">

                    <!-- Icon Grid -->
                    <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-3 max-h-64 overflow-y-auto p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <template x-for="icon in icons" :key="icon">
                            <button
                                type="button"
                                @click="selectedIcon = icon"
                                class="p-3 rounded-lg border-2 transition hover:border-purple-500"
                                :class="selectedIcon === icon ? 'border-purple-500 bg-purple-50' : 'border-gray-200 bg-white'"
                            >
                                <ion-icon :name="icon" style="font-size: 24px;" :style="`color: ${selectedIcon === icon ? color : '#6b7280'}`"></ion-icon>
                            </button>
                        </template>
                    </div>
                    @error('icon')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center gap-3 pt-6 border-t border-gray-200">
                    <button
                        type="submit"
                        class="px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition"
                    >
                        Create Category
                    </button>
                    <a
                        href="{{ route('categories.index') }}"
                        class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition"
                    >
                        Cancel
                    </a>
                </div>

            </form>
        </div>

    </div>
@endsection

@push('scripts')
<!-- Ionicons -->
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<script>
    function categoryForm() {
        return {
            name: '{{ old('name') }}',
            slug: '{{ old('slug') }}',
            color: '{{ old('color', '#7c3aed') }}',
            selectedIcon: '{{ old('icon', 'color-palette') }}',
            icons: [
                'color-palette', 'code-slash', 'briefcase', 'megaphone', 'camera',
                'musical-notes', 'book', 'bulb', 'fitness', 'restaurant',
                'airplane', 'car', 'bicycle', 'globe', 'home',
                'heart', 'star', 'trophy', 'rocket', 'flag',
                'game-controller', 'headset', 'mic', 'desktop', 'phone-portrait',
                'cart', 'card', 'cash', 'calculator', 'pie-chart',
                'bar-chart', 'stats-chart', 'trending-up', 'analytics', 'newspaper',
                'people', 'person', 'school', 'library', 'flask',
                'beaker', 'planet', 'leaf', 'cloud', 'thunderstorm',
                'sunny', 'moon', 'basketball', 'football', 'baseball',
                'hammer', 'construct', 'brush', 'color-fill', 'shapes'
            ],

            generateSlug() {
                this.slug = this.name
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        }
    }
</script>
@endpush
