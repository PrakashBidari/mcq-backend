@extends('layouts.dashboard')

@section('title', 'Purchases')
@section('page-title', 'Purchases')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h3 class="text-xl font-bold text-gray-800">All Purchases</h3>
                <p class="text-sm text-gray-600 mt-1">Completed transactions across question sets, packages, attempt packs and subscriptions</p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <select name="type" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">All types</option>
                    @foreach (['question_set' => 'Question Set', 'package' => 'Package', 'attempt_pack' => 'Attempt Pack', 'subscription' => 'Subscription'] as $value => $label)
                        <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="p-6">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-left py-2">User</th>
                        <th class="text-left py-2">Type</th>
                        <th class="text-left py-2">Item</th>
                        <th class="text-left py-2">Platform</th>
                        <th class="text-left py-2">Price Paid</th>
                        <th class="text-left py-2">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4">{{ $purchase->user->name ?? '—' }}</td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                                    {{ ucfirst(str_replace('_', ' ', $purchase->purchase_type)) }}
                                </span>
                            </td>
                            <td class="py-4 text-sm text-gray-700">
                                {{ $purchase->questionSet->name ?? $purchase->package->name ?? $purchase->attemptPack->name ?? $purchase->subscriptionPlan->name ?? '—' }}
                            </td>
                            <td class="py-4 text-sm text-gray-500 uppercase">{{ $purchase->platform }}</td>
                            <td class="py-4 font-semibold">&yen;{{ number_format($purchase->price_paid) }}</td>
                            <td class="py-4 text-sm text-gray-600">{{ $purchase->purchased_at?->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-gray-500">No purchases yet.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-6">
                {{ $purchases->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
