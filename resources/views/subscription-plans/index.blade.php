@extends('layouts.dashboard')

@section('title', 'Subscription Plans')
@section('page-title', 'Subscription Plans')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Subscription Plans</h3>
                <p class="text-sm text-gray-600 mt-1">One-time purchases that unlock all paid content for a fixed number of days (or forever, for Lifetime).</p>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'SubscriptionPlan'))
                <a href="{{ route('subscription-plans.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition whitespace-nowrap">
                    Add Subscription Plan
                </a>
            @endif
        </div>

        <div class="p-6">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-left py-2">Name</th>
                        <th class="text-left py-2">Product Key</th>
                        <th class="text-left py-2">Duration</th>
                        <th class="text-left py-2">Price</th>
                        <th class="text-left py-2">Status</th>
                        <th class="text-center py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptionPlans as $plan)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 font-semibold text-gray-800">{{ $plan->name }}</td>
                            <td class="py-4"><code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $plan->product_key }}</code></td>
                            <td class="py-4">{{ $plan->duration_days ? $plan->duration_days . ' days' : 'Lifetime' }}</td>
                            <td class="py-4 font-semibold">&yen;{{ number_format($plan->price, 2) }}</td>
                            <td class="py-4">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'SubscriptionPlan'))
                                        <a href="{{ route('subscription-plans.edit', $plan->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">Edit</a>
                                    @endif
                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('delete', 'SubscriptionPlan'))
                                        <form action="{{ route('subscription-plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('Delete this subscription plan?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-gray-500">No subscription plans yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
