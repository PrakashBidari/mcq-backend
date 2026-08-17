@extends('layouts.dashboard')

@section('title', 'Price Tiers')
@section('page-title', 'Price Tiers')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Price Tier Catalog</h3>
                <p class="text-sm text-gray-600 mt-1">
                    Used for one-time Question Set / Package prices. Each tier needs a matching product created in
                    App Store Connect and Google Play Console (with the tier key as the product id suffix) before it's purchasable.
                </p>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'PriceTier'))
                <a href="{{ route('price-tiers.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition whitespace-nowrap">
                    Add Price Tier
                </a>
            @endif
        </div>

        <div class="p-6">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-left py-2">Tier Key</th>
                        <th class="text-left py-2">Label</th>
                        <th class="text-left py-2">Amount</th>
                        <th class="text-left py-2">Status</th>
                        <th class="text-center py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($priceTiers as $tier)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4"><code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $tier->tier_key }}</code></td>
                            <td class="py-4">{{ $tier->label }}</td>
                            <td class="py-4 font-semibold text-gray-800">&yen;{{ number_format($tier->amount, 2) }}</td>
                            <td class="py-4">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $tier->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $tier->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'PriceTier'))
                                        <a href="{{ route('price-tiers.edit', $tier->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">Edit</a>
                                        <form action="{{ route('price-tiers.toggle', $tier->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition">
                                                {{ $tier->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-gray-500">No price tiers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
