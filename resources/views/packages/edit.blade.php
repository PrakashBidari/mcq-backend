@extends('layouts.dashboard')
@section('title', 'Edit Package')
@section('page-title', 'Edit Package')

@section('content')
    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('packages.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                &larr; Back to Packages
            </a>
        </div>

        <div class="rounded-lg bg-white shadow-sm"
            x-data="packageForm({{ $categories->toJson() }}, {{ $package->category_id }}, {{ $availableSets->toJson() }}, {{ $package->questionSets->pluck('id')->toJson() }})">
            <div class="border-b border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-800">Edit Package</h3>
                <p class="mt-1 text-sm text-gray-600">Update package details and its question sets</p>
            </div>

            <form action="{{ route('packages.update', $package->id) }}" method="POST" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-gray-700">Package Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $package->name) }}" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
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
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">{{ old('description', $package->description) }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Question Sets Picker -->
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Question Sets in this Package</label>
                    <div class="max-h-64 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-4">
                        <template x-if="availableSets.length === 0">
                            <p class="text-sm text-gray-500">No available question sets found in this category.</p>
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
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', $package->is_active) ? 'checked' : '' }}
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
                            {{ old('is_paid', $package->is_paid) ? 'checked' : '' }}
                            class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                            onchange="togglePriceField(this)">
                        <span class="ml-3">
                            <span class="text-sm font-semibold text-gray-700">Paid</span>
                            <span class="block text-xs text-gray-500">Require payment to access the whole package</span>
                        </span>
                    </label>
                </div>

                <div id="price_field" class="{{ old('is_paid', $package->is_paid) ? '' : 'hidden' }} space-y-4 rounded-lg border border-gray-200 p-4">
                    <div>
                        <label for="price_tier_id" class="mb-2 block text-sm font-semibold text-gray-700">Price Tier <span class="text-red-500">*</span></label>
                        <select name="price_tier_id" id="price_tier_id" onchange="updatePaidPricePreview(this)"
                            {{ old('is_paid', $package->is_paid) ? 'required' : '' }}
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                            <option value="">Select a price tier</option>
                            @foreach ($priceTiers as $tier)
                                <option value="{{ $tier->id }}" data-amount="{{ $tier->amount }}"
                                    {{ old('price_tier_id', $package->price_tier_id) == $tier->id ? 'selected' : '' }}>
                                    &yen;{{ number_format($tier->amount) }} ({{ $tier->tier_key }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Each tier maps to a matching store product already created in App Store Connect / Google
                            Play Console — pick the tier, don't type a custom amount.
                        </p>
                        @error('price_tier_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <span class="mb-2 block text-sm font-semibold text-gray-700">Paid Price</span>
                        <p id="paid_price_preview" class="text-lg font-bold text-gray-800">&yen;0</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">This purchase grants <span class="text-red-500">*</span></label>
                        <div class="flex gap-6">
                            <label class="flex cursor-pointer items-center">
                                <input type="radio" name="access_type" value="attempts"
                                    {{ old('access_type', $package->access_type ?? 'attempts') === 'attempts' ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700">Attempts</span>
                            </label>
                            <label class="flex cursor-pointer items-center">
                                <input type="radio" name="access_type" value="days"
                                    {{ old('access_type', $package->access_type) === 'days' ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700">Days</span>
                            </label>
                            <label class="flex cursor-pointer items-center">
                                <input type="radio" name="access_type" value="hours"
                                    {{ old('access_type', $package->access_type) === 'hours' ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700">Hours</span>
                            </label>
                            <label class="flex cursor-pointer items-center">
                                <input type="radio" name="access_type" value="minutes"
                                    {{ old('access_type', $package->access_type) === 'minutes' ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700">Minutes</span>
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Pick one unit only. Attempts = number of quiz plays; days / hours / minutes = a time window that starts at purchase.</p>
                        <input type="number" name="access_value" id="access_value" min="1"
                            value="{{ old('access_value', $package->access_value) }}"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g. 3 attempts, 3 days, 6 hours, or 30 minutes">
                        @error('access_value')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <!-- Free Trial -->
                <div>
                    <label class="flex cursor-pointer items-center">
                        <input type="checkbox" name="trial_enabled" id="trial_enabled" value="1"
                            {{ old('trial_enabled', $package->trial_enabled) ? 'checked' : '' }}
                            class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                            onchange="toggleTrialFields(this)">
                        <span class="ml-3">
                            <span class="text-sm font-semibold text-gray-700">Allow Free Trial</span>
                            <span class="block text-xs text-gray-500">Let users try this package before buying</span>
                        </span>
                    </label>
                </div>

                <div id="trial_fields" class="{{ old('trial_enabled', $package->trial_enabled) ? '' : 'hidden' }} space-y-3 rounded-lg border border-gray-200 p-4">
                    <div class="flex gap-6">
                        <label class="flex cursor-pointer items-center">
                            <input type="radio" name="trial_type" value="attempts" {{ old('trial_type', $package->trial_type ?? 'attempts') === 'attempts' ? 'checked' : '' }}
                                class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">Free attempts</span>
                        </label>
                        <label class="flex cursor-pointer items-center">
                            <input type="radio" name="trial_type" value="days" {{ old('trial_type', $package->trial_type) === 'days' ? 'checked' : '' }}
                                class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">Free days</span>
                        </label>
                    </div>
                    <input type="number" name="trial_value" id="trial_value" min="1" value="{{ old('trial_value', $package->trial_value) }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                    @error('trial_value')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                    <button type="submit" class="rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-700">Update Package</button>
                    <a href="{{ route('packages.index') }}" class="rounded-lg bg-gray-100 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-200">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            updatePaidPricePreview(document.getElementById('price_tier_id'));
        });

        function togglePriceField(checkbox) {
            const priceField = document.getElementById('price_field');
            const priceTierSelect = document.getElementById('price_tier_id');
            const accessValue = document.getElementById('access_value');
            if (checkbox.checked) {
                priceField.classList.remove('hidden');
                priceTierSelect.required = true;
                accessValue.required = true;
            } else {
                priceField.classList.add('hidden');
                priceTierSelect.required = false;
                priceTierSelect.value = '';
                accessValue.required = false;
                accessValue.value = '';
                updatePaidPricePreview(priceTierSelect);
            }
        }

        function updatePaidPricePreview(select) {
            const option = select.options[select.selectedIndex];
            const amount = option ? Number(option.dataset.amount || 0) : 0;
            document.getElementById('paid_price_preview').textContent = '¥' + amount.toLocaleString();
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

        function packageForm(categories, initialCategoryId, initialAvailableSets, initialSelectedIds) {
            return {
                categories: categories,
                categoryId: '{{ old('category_id') }}' || initialCategoryId,
                subcategoryId: '{{ old('subcategory_id', $package->subcategory_id) }}',
                subcategories: [],
                availableSets: initialAvailableSets,
                selectedSetIds: {!! json_encode(array_map('intval', old('question_set_ids', $package->questionSets->pluck('id')->all()))) !!},

                init() {
                    const cat = this.categories.find(c => c.id == this.categoryId);
                    this.subcategories = cat ? cat.children : [];
                },

                onCategoryChange() {
                    this.subcategoryId = '';
                    this.selectedSetIds = [];

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

                    fetch(`{{ route('packages.available-sets') }}?category_id=${effectiveCategoryId}&package_id={{ $package->id }}`, {
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
