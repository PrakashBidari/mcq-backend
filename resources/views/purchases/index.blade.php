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
            <div class="flex items-center gap-2">
                <form method="GET" class="flex items-center gap-2">
                    <select name="type" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">All types</option>
                        @foreach (['question_set' => 'Question Set', 'package' => 'Package', 'attempt_pack' => 'Attempt Pack', 'subscription' => 'Subscription'] as $value => $label)
                            <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('purchases.grant-form') }}" class="px-3 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold hover:bg-purple-700">
                    Grant access
                </a>
            </div>
        </div>

        <div class="p-6 overflow-x-auto">
            <table id="purchasesTable" class="w-full min-w-max">
                <thead>
                    <tr>
                        <th class="text-left py-2">User</th>
                        <th class="text-left py-2">Email</th>
                        <th class="text-left py-2">Type</th>
                        <th class="text-left py-2">Item</th>
                        <th class="text-left py-2">Platform</th>
                        <th class="text-left py-2">Price Paid</th>
                        <th class="text-left py-2">Access Left</th>
                        <th class="text-left py-2">Purchased</th>
                        <th class="text-left py-2">Expires</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $purchase)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4">{{ $purchase->user->name ?? '—' }}</td>
                            <td class="py-4 text-sm text-gray-600">{{ $purchase->user->email ?? '—' }}</td>
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
                            <td class="py-4 text-sm">
                                <span class="{{ ($purchase->access_is_active ?? false) ? 'text-green-700' : 'text-red-600' }}">
                                    {{ $purchase->access_remaining_label ?? '—' }}
                                </span>
                            </td>
                            <td class="py-4 text-sm text-gray-600" data-order="{{ $purchase->purchased_at?->timestamp }}">
                                {{ $purchase->purchased_at?->format('M d, Y H:i') ?? '—' }}
                            </td>
                            <td class="py-4 text-sm text-gray-600" data-order="{{ $purchase->expires_at?->timestamp ?? 0 }}">
                                {{ $purchase->expires_at?->format('M d, Y') ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#purchasesTable').DataTable({
            pageLength: 6,
            lengthMenu: [6, 12, 24, 50],
            order: [[7, 'desc']],
            columnDefs: [{ orderable: false, targets: [2] }],
            language: {
                search: "Filter:",
                lengthMenu: "Show _MENU_ per page",
                info: "Showing _START_ to _END_ of _TOTAL_ purchases",
                infoEmpty: "No purchases",
                infoFiltered: "(filtered from _MAX_ total)",
                zeroRecords: "No matching purchases"
            }
        });
    });
</script>
@endpush
