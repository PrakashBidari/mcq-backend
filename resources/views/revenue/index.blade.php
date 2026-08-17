@extends('layouts.dashboard')

@section('title', 'Revenue')
@section('page-title', 'Revenue')

@section('content')
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <p class="text-sm text-gray-600">Total Revenue (All Time)</p>
        <p class="text-4xl font-bold text-purple-700 mt-1">&yen;{{ number_format($grandTotal) }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        @foreach (['question_set' => 'Question Sets', 'package' => 'Packages', 'attempt_pack' => 'Attempt Packs', 'subscription' => 'Subscriptions'] as $type => $label)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <p class="text-sm text-gray-600">{{ $label }}</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">&yen;{{ number_format($totalsByType[$type]->total ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $totalsByType[$type]->count ?? 0 }} purchases</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800">Monthly Revenue</h3>
            <p class="text-sm text-gray-600 mt-1">Last 12 months with any completed purchase</p>
        </div>
        <div class="p-6">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-left py-2">Month</th>
                        <th class="text-left py-2">Purchases</th>
                        <th class="text-left py-2">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthly as $row)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4">{{ $row->month }}</td>
                            <td class="py-4">{{ $row->count }}</td>
                            <td class="py-4 font-semibold">&yen;{{ number_format($row->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-gray-500">No revenue data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
