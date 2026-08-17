@extends('layouts.dashboard')
@section('title', 'Create Package')
@section('page-title', 'Create New Package')

@section('content')
    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('packages.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                &larr; Back to Packages
            </a>
        </div>

        <div class="rounded-lg bg-white shadow-sm" x-data="packageForm({{ $categories->toJson() }})">
            <div class="border-b border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-800">Package Information</h3>
                <p class="mt-1 text-sm text-gray-600">Give the package a name, pick where it lives, then choose the question sets to bundle</p>
            </div>

            <form action="{{ route('packages.store') }}" method="POST" class="space-y-6 p-6">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-gray-700">Package Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="e.g., SSW Hotel Complete Pack">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="mb-2 block text-sm font-semibold text-gray-700">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" id="category_id" x-model="categoryId" @change="onCategoryChange()" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                        <option value="">Select a category</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name" :selected="cat.id == categoryId"></option>
                        </template>
                    </select>
                    @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Subcategory -->
                <div x-show="subcategories.length > 0">
                    <label for="subcategory_id" class="mb-2 block text-sm font-semibold text-gray-700">Subcategory</label>
                    <select name="subcategory_id" id="subcategory_id" x-model="subcategoryId" @change="onSubcategoryChange()"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                        <option value="">None</option>
                        <template x-for="sub in subcategories" :key="sub.id">
                            <option :value="sub.id" x-text="sub.name"></option>
                        </template>
                    </select>
                    @error('subcategory_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="mb-2 block text-sm font-semibold text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Question Sets Picker -->
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Question Sets in this Package</label>
                    <p class="mb-2 text-xs text-gray-500" x-show="!categoryId">Select a category first to see its available (not-yet-packaged) question sets.</p>
                    <div class="max-h-64 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-4" x-show="categoryId">
                        <template x-if="availableSets.length === 0">
                            <p class="text-sm text-gray-500">No unpackaged question sets found in this category.</p>
                        </template>
                        <template x-for="set in availableSets" :key="set.id">
                            <label class="flex cursor-pointer items-center gap-2 py-1">
                                <input type="checkbox" name="question_set_ids[]" :value="set.id"
                                    :checked="selectedSetIds.includes(set.id)"
                                    @change="toggleSet(set.id)"
                                    class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span x-text="set.name"></span>
                                <span class="text-xs" :class="set.is_paid ? 'text-amber-600' : 'text-green-600'" x-text="set.is_paid ? '(paid)' : '(free)'"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Active Status -->
                <div>
                    <label class="flex cursor-pointer items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked
                            class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-3">
                            <span class="text-sm font-semibold text-gray-700">Active</span>
                            <span class="block text-xs text-gray-500">Make this package available</span>
                        </span>
                    </label>
                </div>

                <!-- Paid Status -->
                <div>
                    <label class="flex cursor-pointer items-center">
                        <input type="checkbox" name="is_paid" id="is_paid" value="1"
                            {{ old('is_paid') ? 'checked' : '' }}
                            class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                            onchange="togglePriceField(this)">
                        <span class="ml-3">
                            <span class="text-sm font-semibold text-gray-700">Paid</span>
                            <span class="block text-xs text-gray-500">Require payment to access the whole package</span>
                        </span>
                    </label>
                </div>

                <div id="price_field" class="{{ old('is_paid') ? '' : 'hidden' }} space-y-4 rounded-lg border border-gray-200 p-4">
                    <div>
                        <label for="price" class="mb-2 block text-sm font-semibold text-gray-700">Price <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-gray-500">&yen;</span>
                            <input type="number" name="price" id="price" min="0.01" step="0.01" value="{{ old('price') }}"
                                class="w-full rounded-lg border border-gray-300 py-3 pl-10 pr-4 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                placeholder="e.g. 300">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Any amount in yen. A matching store product must exist in App Store Connect / Google
                            Play Console at this exact price before it can be bought in the app.
                        </p>
                        @error('price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">This purchase grants <span class="text-red-500">*</span></label>
                        <div class="flex gap-6">
                            <label class="flex cursor-pointer items-center">
                                <input type="radio" name="access_type" value="attempts"
                                    {{ old('access_type', 'attempts') === 'attempts' ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700">Attempts</span>
                            </label>
                            <label class="flex cursor-pointer items-center">
                                <input type="radio" name="access_type" value="days"
                                    {{ old('access_type') === 'days' ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700">Days</span>
                            </label>
                        </div>
                        <input type="number" name="access_value" id="access_value" min="1"
                            value="{{ old('access_value') }}"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g. 3 attempts, or 3 days">
                        @error('access_value')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Free Trial -->
                <div>
                    <label class="flex cursor-pointer items-center">
                        <input type="checkbox" name="trial_enabled" id="trial_enabled" value="1"
                            {{ old('trial_enabled') ? 'checked' : '' }}
                            class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                            onchange="toggleTrialFields(this)">
                        <span class="ml-3">
                            <span class="text-sm font-semibold text-gray-700">Allow Free Trial</span>
                            <span class="block text-xs text-gray-500">Let users try this package before buying</span>
                        </span>
                    </label>
                </div>

                <div id="trial_fields" class="{{ old('trial_enabled') ? '' : 'hidden' }} space-y-3 rounded-lg border border-gray-200 p-4">
                    <div class="flex gap-6">
                        <label class="flex cursor-pointer items-center">
                            <input type="radio" name="trial_type" value="attempts" {{ old('trial_type', 'attempts') === 'attempts' ? 'checked' : '' }}
                                class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">Free attempts</span>
                        </label>
                        <label class="flex cursor-pointer items-center">
                            <input type="radio" name="trial_type" value="days" {{ old('trial_type') === 'days' ? 'checked' : '' }}
                                class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">Free days</span>
                        </label>
                    </div>
                    <input type="number" name="trial_value" id="trial_value" min="1" value="{{ old('trial_value') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="e.g. 1 attempt, or 3 days">
                    @error('trial_value')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                    <button type="submit" class="rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-700">Create Package</button>
                    <a href="{{ route('packages.index') }}" class="rounded-lg bg-gray-100 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-200">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePriceField(checkbox) {
            const priceField = document.getElementById('price_field');
            const priceInput = document.getElementById('price');
            const accessValue = document.getElementById('access_value');
            if (checkbox.checked) {
                priceField.classList.remove('hidden');
                priceInput.required = true;
                accessValue.required = true;
            } else {
                priceField.classList.add('hidden');
                priceInput.required = false;
                priceInput.value = '';
                accessValue.required = false;
                accessValue.value = '';
            }
        }

        function toggleTrialFields(checkbox) {
            const trialFields = document.getElementById('trial_fields');
            const trialValue = document.getElementById('trial_value');
            if (checkbox.checked) {
                trialFields.classList.remove('hidden');
                trialValue.required = true;
            } else {
                trialFields.classList.add('hidden');
                trialValue.required = false;
                trialValue.value = '';
            }
        }

        function packageForm(categories) {
            return {
                categories: categories,
                categoryId: '{{ old('category_id') }}',
                subcategoryId: '{{ old('subcategory_id') }}',
                subcategories: [],
                availableSets: [],
                selectedSetIds: {!! json_encode(array_map('intval', old('question_set_ids', []))) !!},

                init() {
                    if (this.categoryId) {
                        this.onCategoryChange(false);
                    }
                },

                onCategoryChange(resetSelection = true) {
                    if (resetSelection) {
                        this.subcategoryId = '';
                        this.selectedSetIds = [];
                    }

                    const cat = this.categories.find(c => c.id == this.categoryId);
                    this.subcategories = cat ? cat.children : [];

                    this.fetchAvailableSets();
                },

                onSubcategoryChange() {
                    this.selectedSetIds = [];
                    this.fetchAvailableSets();
                },

                fetchAvailableSets() {
                    // Question sets are tagged with whichever category they actually live
                    // under - the subcategory itself when one is picked, otherwise the
                    // top-level category.
                    const effectiveCategoryId = this.subcategoryId || this.categoryId;

                    if (!effectiveCategoryId) {
                        this.availableSets = [];
                        return;
                    }

                    fetch(`{{ route('packages.available-sets') }}?category_id=${effectiveCategoryId}`, {
                        headers: { 'Accept': 'application/json' }
                    })
                        .then(r => r.json())
                        .then(res => { this.availableSets = res.data || []; })
                        .catch(() => { this.availableSets = []; });
                },

                toggleSet(id) {
                    const idx = this.selectedSetIds.indexOf(id);
                    if (idx === -1) {
                        this.selectedSetIds.push(id);
                    } else {
                        this.selectedSetIds.splice(idx, 1);
                    }
                }
            }
        }
    </script>
@endpush
