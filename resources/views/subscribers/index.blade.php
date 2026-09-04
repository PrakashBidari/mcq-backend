@extends('layouts.dashboard')

@section('title', 'Subscribers')
@section('page-title', 'Subscribers')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-sm text-gray-600">Users With Any Paid Purchase</p>
            <p class="text-3xl font-bold text-green-700 mt-1">{{ $paidUsersCount }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-sm text-gray-600">On an Active Subscription Plan</p>
            <p class="text-3xl font-bold text-purple-700 mt-1">{{ $activeSubscriberCount }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="text-sm text-gray-600">Free Users (No Purchases)</p>
            <p class="text-3xl font-bold text-gray-700 mt-1">{{ $freeUsersCount }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800">Subscribers</h3>
            <p class="text-sm text-gray-600 mt-1">Every user who has bought a question set or package, and what's left of the access they paid for</p>
        </div>

        <div class="p-6 overflow-x-auto">
            <table id="subscribersTable" class="w-full min-w-max">
                <thead>
                    <tr>
                        <th class="text-left py-2">User</th>
                        <th class="text-left py-2">Email</th>
                        <th class="text-left py-2">Type</th>
                        <th class="text-left py-2">Set / Package</th>
                        <th class="text-left py-2">Access Left</th>
                        <th class="text-left py-2">Status</th>
                        <th class="text-left py-2">Taken On</th>
                        <th class="text-left py-2">Expires</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4">{{ $row->user_name }}</td>
                            <td class="py-4 text-sm text-gray-600">{{ $row->user_email }}</td>
                            <td class="py-4">
                                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">{{ $row->type }}</span>
                            </td>
                            <td class="py-4 text-sm text-gray-700">{{ $row->item_name }}</td>
                            <td class="py-4 text-sm">{{ $row->remaining }}</td>
                            <td class="py-4">
                                @if ($row->is_active)
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Active</span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Ended</span>
                                @endif
                            </td>
                            <td class="py-4 text-sm text-gray-600" data-order="{{ $row->taken_at?->timestamp ?? 0 }}">
                                {{ $row->taken_at?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="py-4 text-sm text-gray-600" data-order="{{ $row->expires_at?->timestamp ?? 0 }}">
                                {{ $row->expires_at?->format('M d, Y') ?? '—' }}
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
        $('#subscribersTable').DataTable({
            pageLength: 6,
            lengthMenu: [6, 12, 24, 50],
            order: [[6, 'desc']],
            columnDefs: [{ orderable: false, targets: [2, 5] }],
            language: {
                search: "Filter:",
                lengthMenu: "Show _MENU_ per page",
                info: "Showing _START_ to _END_ of _TOTAL_ subscribers",
                infoEmpty: "No subscribers yet",
                infoFiltered: "(filtered from _MAX_ total)",
                zeroRecords: "No matching subscribers"
            }
        });
    });
</script>
@endpush
