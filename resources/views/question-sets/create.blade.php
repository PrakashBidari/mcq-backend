@extends('layouts.dashboard')
@section('title', 'Create Question Set')
@section('page-title', 'Create New Question Set')

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
                <h3 class="text-xl font-bold text-gray-800">Question Set Information</h3>
                <p class="mt-1 text-sm text-gray-600">Fill in the details to create a new question set</p>
            </div>

            <form action="{{ route('question-sets.store') }}" method="POST" class="space-y-6 p-6">
                @csrf

                <!-- Category -->
                <div>
                    <label for="category_id" class="mb-2 block text-sm font-semibold text-gray-700">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select name="category_id" id="category_id" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                        <option value="">Select a category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-gray-700">
                        Question Set Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="e.g., UI/UX Fundamentals">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="mb-2 block text-sm font-semibold text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                        placeholder="Brief description...">{{ old('description') }}</textarea>
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
                        <input type="number" name="time_limit" id="time_limit" value="{{ old('time_limit') }}"
                            min="1" max="180"
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
                            {{ old('is_active', true) ? 'checked' : '' }}
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
                            {{ old('is_paid') ? 'checked' : '' }}
                            class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                            onchange="togglePriceField(this)">
                        <span class="ml-3">
                            <span class="text-sm font-semibold text-gray-700">Paid</span>
                            <span class="block text-xs text-gray-500">Require payment to access</span>
                        </span>
                    </label>
                </div>

                <!-- Price Field -->
                <div id="price_field" class="{{ old('is_paid') ? '' : 'hidden' }}">
                    <label for="price" class="mb-2 block text-sm font-semibold text-gray-700">
                        Price <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-semibold text-gray-500">$</span>
                        <input type="number" name="price" id="price" value="{{ old('price') }}" min="0"
                            step="0.01"
                            class="w-full rounded-lg border border-gray-300 py-3 pl-8 pr-4 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                            placeholder="0.00">
                    </div>
                    @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                    <button type="submit"
                        class="rounded-lg bg-purple-600 px-6 py-3 font-semibold text-white transition hover:bg-purple-700">
                        Create Question Set
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
            if (checkbox.checked) {
                priceField.classList.remove('hidden');
                priceInput.required = true;
            } else {
                priceField.classList.add('hidden');
                priceInput.required = false;
                priceInput.value = '';
            }
        }
    </script>
@endpush
