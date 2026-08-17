@extends('layouts.dashboard')
@section('title', 'Create Price Tier')
@section('page-title', 'Create Price Tier')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('price-tiers.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">&larr; Back to Price Tiers</a>
        </div>

        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Price Tier</h3>
                <p class="mt-1 text-sm text-gray-600">Remember to also create the matching IAP product in App Store Connect / Google Play Console using this tier key.</p>
            </div>

            <form action="{{ route('price-tiers.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <div>
                    <label for="tier_key" class="block text-sm font-semibold text-gray-700 mb-2">Tier Key <span class="text-red-500">*</span></label>
                    <input type="text" name="tier_key" id="tier_key" value="{{ old('tier_key') }}" required
                        pattern="[a-z0-9_]+" placeholder="e.g., tier_650"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <p class="mt-1 text-xs text-gray-500">Lowercase letters, numbers, underscores only. Cannot be changed after creation.</p>
                    @error('tier_key')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="label" class="block text-sm font-semibold text-gray-700 mb-2">Label</label>
                    <input type="text" name="label" id="label" value="{{ old('label') }}" placeholder="e.g., ¥650"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    @error('label')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">Amount (JPY) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required min="0.01" step="0.01"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-semibold text-gray-700 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    @error('sort_order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="flex cursor-pointer items-center">
                        <input type="checkbox" name="is_active" value="1" checked
                            class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-3 text-sm font-semibold text-gray-700">Active</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-6 border-t border-gray-200">
                    <button type="submit" class="px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition">Create Price Tier</button>
                    <a href="{{ route('price-tiers.index') }}" class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
