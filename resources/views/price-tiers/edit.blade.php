@extends('layouts.dashboard')
@section('title', 'Edit Price Tier')
@section('page-title', 'Edit Price Tier')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('price-tiers.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">&larr; Back to Price Tiers</a>
        </div>

        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Edit Price Tier</h3>
            </div>

            <form action="{{ route('price-tiers.update', $priceTier->id) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="tier_key" class="block text-sm font-semibold text-gray-700 mb-2">Tier Key <span class="text-red-500">*</span></label>
                    <input type="text" name="tier_key" id="tier_key" value="{{ old('tier_key', $priceTier->tier_key) }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 font-mono text-sm">
                    <p class="mt-1 text-xs text-gray-500">Baked into the store product id (iOS: <code>{{ config('price_tiers.bundle_id') }}.&lt;tier key&gt;</code>, Android: <code>&lt;tier key&gt;</code>). It must exactly match the product you created in App Store Connect / Google Play Console — changing it here will break existing store products unless you update them too.</p>
                    @error('tier_key')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="label" class="block text-sm font-semibold text-gray-700 mb-2">Label</label>
                    <input type="text" name="label" id="label" value="{{ old('label', $priceTier->label) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    @error('label')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">Amount (JPY) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount', (int) $priceTier->amount) }}" required min="1" step="1"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <p class="mt-1 text-xs text-gray-500">Whole yen only — JPY has no decimal subunit. Changing this only updates the price shown for future purchases — it must still match the price configured in the store console for this product.</p>
                    @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-semibold text-gray-700 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $priceTier->sort_order) }}" min="0"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    @error('sort_order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="flex cursor-pointer items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $priceTier->is_active) ? 'checked' : '' }}
                            class="h-5 w-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-3 text-sm font-semibold text-gray-700">Active</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-6 border-t border-gray-200">
                    <button type="submit" class="px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition">Update Price Tier</button>
                    <a href="{{ route('price-tiers.index') }}" class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
