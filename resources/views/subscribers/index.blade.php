@extends('layouts.dashboard')

@section('title', 'Subscribers')
@section('page-title', 'Subscribers')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-sm text-gray-600">Active Subscribers</p>
            <p class="text-3xl font-bold text-purple-700 mt-1">{{ $activeSubscriberCount }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-sm text-gray-600">Users With Any Paid Purchase</p>
            <p class="text-3xl font-bold text-green-700 mt-1">{{ $paidUsersCount }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-sm text-gray-600">Free Users (No Purchases)</p>
            <p class="text-3xl font-bold text-gray-700 mt-1">{{ $freeUsersCount }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800">Active Subscriptions</h3>
            <p class="text-sm text-gray-600 mt-1">Users currently on an active (non-expired) subscription plan</p>
        </div>

        <div class="p-6">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-left py-2">User</th>
                        <th class="text-left py-2">Plan</th>
                        <th class="text-left py-2">Started</th>
                        <th class="text-left py-2">Expires</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeSubscriptions as $subscription)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4">{{ $subscription->user->name ?? '—' }}</td>
                            <td class="py-4">{{ $subscription->plan->name ?? '—' }}</td>
                            <td class="py-4 text-sm text-gray-600">{{ $subscription->starts_at?->format('M d, Y') }}</td>
                            <td class="py-4 text-sm text-gray-600">{{ $subscription->expires_at?->format('M d, Y') ?? 'Lifetime' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 text-center text-gray-500">No active subscribers.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
