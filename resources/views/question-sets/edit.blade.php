@extends('layouts.dashboard')
@section('title', 'Edit Question Set')
@section('page-title', 'Edit Question Set')

@section('content')
    <div class="max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('question-sets.index') }}"
                class="inline-flex items-center text-purple-600 hover:text-purple-700">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Question Sets
            </a>
        </div>

        <div class="rounded-lg bg-white shadow-sm">
            <div class="border-b border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-800">Edit Question Set</h3>
                <p class="mt-1 text-sm text-gray-600">Update question set information</p>
            </div>

            <form action="{{ route('question-sets.update', $questionSet->id) }}" method="POST" class="space-y-6 p-6">
                @csrf
                @method('PUT')

                <!-- Category (any depth) -->
                <div x-data="categorySelector(@js($categories), @js($selectedPath))">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <template x-for="(options, level) in levels" :key="level">
                            <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700">
                                <span x-text="level === 0 ? 'Category' : 'Subcategory (level ' + (level + 1) + ')'"></span>
                                <span class="text-red-500" x-show="level === 0">*</span>
                                <span class="ml-1 font-normal text-gray-400" x-show="level > 0">— optional</span>
                            </label>
                            <select :required="level === 0" @change="selectCategory(level, $event.target.value)"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                                <option value="" x-text="level === 0 ? 'Select a category' : 'None'"></option>
                                <template x-for="opt in options" :key="opt.id">
                                    <option :value="opt.id" x-text="opt.name" :selected="opt.id == path[level]"></option>
                                </template>
                            </select>
                            </div>
                        </template>
                    </div>

                    <input type="hidden" name="category_id" :value="path.length ? path[path.length - 1] : ''">
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Package -->
                <div>
                    <label for="package_id" class="mb-2 block text-sm font-semibold text-gray-700">
                        Package
                        <span class="ml-1 font-normal text-gray-400">— optional</span>
                    </label>
                    <select name="package_id" id="package_id"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                        <option value="">Not in a package — sold individually</option>
                        @foreach ($packages as $package)
                            <option value="{{ $package->id }}" {{ old('package_id', $questionSet->package_id) == $package->id ? 'selected' : '' }}>
                                {{ $package->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        If assigned to a package, this set is sold only as part of that package — it will not appear
                        in the single-question-set purchase area, and its own price below is ignored.
                    </p>
                    @error('package_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-gray-700">
                        Question Set Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $questionSet->name) }}"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="mb-2 block text-sm font-semibold text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">{{ old('description', $questionSet->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Time Limit -->
                <div>
                    <label for="time_limit" class="mb-2 block text-sm font-semibold text-gray-700">
                        Time Limit (minutes)
                        <span class="ml-1 font-normal text-gray-400">— leave empty for no limit</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <input type="number" name="time_limit" id="time_limit"
                            value="{{ old('time_limit', $questionSet->time_limit) }}" min="1" max="180"
                            class="w-full rounded-lg border border-gray-300 py-3 pl-12 pr-4 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g., 10 (for 10 minutes)">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Allowed: 1–180 minutes. Timer will count down during the quiz.</p>
                    @error('time_limit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pass Percentage -->
                <div>
                    <label for="pass_percentage" class="mb-2 block text-sm font-semibold text-gray-700">
                        Pass Percentage (%)
                        <span class="ml-1 font-normal text-gray-400">— leave empty for the default (60%)</span>
                    </label>
                    <input type="number" name="pass_percentage" id="pass_percentage" value="{{ old('pass_percentage', $questionSet->pass_percentage) }}"
                        min="1" max="100" step="1"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="e.g., 70 (70% correct needed to pass)">
                    <p class="mt-1 text-xs text-gray-500">Allowed: 1–100. The app's result screen shows Passed / Not passed against this mark.</p>
                    @error('pass_percentage')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div>
                    <label class="flex cursor-pointer items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', $questionSet->is_active) ? 'checked' : '' }}
                            class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-3">
                            <span class="text-sm font-semibold text-gray-700">Active</span>
                            <span class="block text-xs text-gray-500">Make this question set available</span>
                        </span>
                    </label>
                </div>

                <!-- Paid Status -->
                <div>
                    <label class="flex cursor-pointer items-center">
                        <input type="checkbox" name="is_paid" id="is_paid" value="1"
                            {{ old('is_paid', $questionSet->is_paid) ? 'checked' : '' }}
                            class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                            onchange="togglePriceField(this)">
                        <span class="ml-3">
                            <span class="text-sm font-semibold text-gray-700">Paid</span>
                            <span class="block text-xs text-gray-500">Require payment to access</span>
                        </span>
                    </label>
                </div>

                <!-- Price + Access Grant Field -->
                <div id="price_field" class="{{ old('is_paid', $questionSet->is_paid) ? '' : 'hidden' }} space-y-4 rounded-lg border border-gray-200 p-4">
                    <div>
                        <label for="price_tier_id" class="mb-2 block text-sm font-semibold text-gray-700">
                            Price Tier <span class="text-red-500">*</span>
                        </label>
                        <select name="price_tier_id" id="price_tier_id" onchange="updatePaidPricePreview(this)"
                            {{ old('is_paid', $questionSet->is_paid) ? 'required' : '' }}
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                            <option value="">Select a price tier</option>
                            @foreach ($priceTiers as $tier)
                                <option value="{{ $tier->id }}" data-amount="{{ $tier->amount }}"
                                    {{ old('price_tier_id', $questionSet->price_tier_id) == $tier->id ? 'selected' : '' }}>
                                    &yen;{{ number_format($tier->amount) }} ({{ $tier->tier_key }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Each tier maps to a matching store product already created in App Store Connect / Google
                            Play Console — pick the tier, don't type a custom amount.
                        </p>
                        @error('price_tier_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <span class="mb-2 block text-sm font-semibold text-gray-700">Paid Price</span>
                        <p id="paid_price_preview" class="text-lg font-bold text-gray-800">&yen;0</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-700">
                            This purchase grants <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-6">
                            <label class="flex cursor-pointer items-center">
                                <input type="radio" name="access_type" value="attempts"
                                    {{ old('access_type', $questionSet->access_type ?? 'attempts') === 'attempts' ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700">Attempts</span>
                            </label>
                            <label class="flex cursor-pointer items-center">
                                <input type="radio" name="access_type" value="days"
                                    {{ old('access_type', $questionSet->access_type) === 'days' ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700">Days</span>
                            </label>
                            <label class="flex cursor-pointer items-center">
                                <input type="radio" name="access_type" value="hours"
                                    {{ old('access_type', $questionSet->access_type) === 'hours' ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700">Hours</span>
                            </label>
                            <label class="flex cursor-pointer items-center">
                                <input type="radio" name="access_type" value="minutes"
                                    {{ old('access_type', $questionSet->access_type) === 'minutes' ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700">Minutes</span>
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Pick one unit only. Attempts = number of quiz plays; days / hours / minutes = a time window that starts at purchase.</p>
                        <input type="number" name="access_value" id="access_value" min="1"
                            value="{{ old('access_value', $questionSet->access_value) }}"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g. 3 attempts, 3 days, 6 hours, or 30 minutes">
                        @error('access_value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Free Trial -->
                <div>
                    <label class="flex cursor-pointer items-center">
                        <input type="checkbox" name="trial_enabled" id="trial_enabled" value="1"
                            {{ old('trial_enabled', $questionSet->trial_enabled) ? 'checked' : '' }}
                            class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                            onchange="toggleTrialFields(this)">
                        <span class="ml-3">
                            <span class="text-sm font-semibold text-gray-700">Allow Free Trial</span>
                            <span class="block text-xs text-gray-500">Let users try this paid set before buying</span>
                        </span>
                    </label>
                </div>

                <div id="trial_fields" class="{{ old('trial_enabled', $questionSet->trial_enabled) ? '' : 'hidden' }} space-y-3 rounded-lg border border-gray-200 p-4">
                    <div class="flex gap-6">
                        <label class="flex cursor-pointer items-center">
                            <input type="radio" name="trial_type" value="attempts"
                                {{ old('trial_type', $questionSet->trial_type ?? 'attempts') === 'attempts' ? 'checked' : '' }}
                                class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">Free attempts</span>
                        </label>
                        <label class="flex cursor-pointer items-center">
                            <input type="radio" name="trial_type" value="days"
                                {{ old('trial_type', $questionSet->trial_type) === 'days' ? 'checked' : '' }}
                                class="h-4 w-4 border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-sm text-gray-700">Free days</span>
                        </label>
                    </div>
                    <div>
                        <input type="number" name="trial_value" id="trial_value" min="1"
                            value="{{ old('trial_value', $questionSet->trial_value) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g. 1 attempt, or 3 days">
                        @error('trial_value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Stats -->
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Questions:</strong> {{ $questionSet->questions_count }} questions in this set
                    </p>
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                    <button type="submit"
                        class="rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-700">
                        Update Question Set
                    </button>
                    <a href="{{ route('question-sets.index') }}"
                        class="rounded-lg bg-gray-100 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-200">
                        Cancel
                    </a>
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

        function categorySelector(categories, initialPath) {
            return {
                categories: categories,
                // Selected category ids, top-level first; the last one is where the set lives
                path: initialPath || [],

                // One entry per dropdown: the top-level categories, then the children of
                // each category picked so far.
                get levels() {
                    const levels = [this.categories];
                    let options = this.categories;

                    for (const id of this.path) {
                        const node = options.find(c => c.id == id);
                        if (!node || !node.children.length) break;
                        options = node.children;
                        levels.push(options);
                    }

                    return levels;
                },

                selectCategory(level, value) {
                    this.path = this.path.slice(0, level);
                    if (value) this.path.push(Number(value));
                }
            }
        }
    </script>
@endpush
