@extends('layouts.dashboard')
@section('title', 'Edit Subscription Plan')
@section('page-title', 'Edit Subscription Plan')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('subscription-plans.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">&larr; Back to Subscription Plans</a>
        </div>

        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Edit Subscription Plan</h3>
            </div>

            <form action="{{ route('subscription-plans.update', $subscriptionPlan->id) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $subscriptionPlan->name) }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Product Key</label>
                    <code class="block px-4 py-3 bg-gray-100 rounded-lg text-sm">{{ $subscriptionPlan->product_key }}</code>
                    <p class="mt-1 text-xs text-gray-500">Product keys can't be changed after creation.</p>
                </div>

                <div>
                    <label class="flex cursor-pointer items-center">
                        <input type="checkbox" name="is_lifetime" id="is_lifetime" value="1"
                            {{ old('is_lifetime', is_null($subscriptionPlan->duration_days)) ? 'checked' : '' }}
                            onchange="toggleDurationField(this)"
                            class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-3">
                            <span class="text-sm font-semibold text-gray-700">Lifetime</span>
                            <span class="block text-xs text-gray-500">Never expires</span>
                        </span>
                    </label>
                </div>

                <div id="duration_field" class="{{ old('is_lifetime', is_null($subscriptionPlan->duration_days)) ? 'hidden' : '' }}">
                    <label for="duration_days" class="block text-sm font-semibold text-gray-700 mb-2">Duration (days) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_days" id="duration_days" value="{{ old('duration_days', $subscriptionPlan->duration_days) }}" min="1"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    @error('duration_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">Price (JPY) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" id="price" value="{{ old('price', $subscriptionPlan->price) }}" required min="0.01" step="0.01"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    @error('price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-semibold text-gray-700 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $subscriptionPlan->sort_order) }}" min="0"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    @error('sort_order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="flex cursor-pointer items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $subscriptionPlan->is_active) ? 'checked' : '' }}
                            class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-3 text-sm font-semibold text-gray-700">Active</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-6 border-t border-gray-200">
                    <button type="submit" class="px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition">Update Subscription Plan</button>
                    <a href="{{ route('subscription-plans.index') }}" class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleDurationField(checkbox) {
            const field = document.getElementById('duration_field');
            const input = document.getElementById('duration_days');
            if (checkbox.checked) {
                field.classList.add('hidden');
                input.required = false;
            } else {
                field.classList.remove('hidden');
                input.required = true;
            }
        }
    </script>
@endpush
