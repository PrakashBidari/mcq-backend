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

                <!-- Category -->
                <div>
                    <label for="category_id" class="mb-2 block text-sm font-semibold text-gray-700">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select name="category_id" id="category_id" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                        <option value="">Select a category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category_id', $questionSet->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->parent_id ? '— ' : '' }}{{ $category->name }}
                            </option>
                        @endforeach
                    </select>
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
                        <label for="price" class="mb-2 block text-sm font-semibold text-gray-700">
                            Price <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-gray-500">&yen;</span>
                            <input type="number" name="price" id="price" min="0.01" step="0.01"
                                value="{{ old('price', $questionSet->price) }}"
                                {{ old('is_paid', $questionSet->is_paid) ? 'required' : '' }}
                                class="w-full rounded-lg border border-gray-300 py-3 pl-10 pr-4 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                                placeholder="e.g. 300">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Any amount in yen. A matching store product must exist in App Store Connect / Google
                            Play Console at this exact price before it can be bought in the app.
                        </p>
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                        </div>
                        <input type="number" name="access_value" id="access_value" min="1"
                            value="{{ old('access_value', $questionSet->access_value) }}"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="e.g. 3 attempts, or 3 days">
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
    </script>
@endpush
