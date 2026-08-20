@extends('layouts.dashboard')

@php
    $roleTitles = ['admin' => 'Admins', 'teacher' => 'Teachers', 'user' => 'Users'];
    $title = $roleTitles[$role] ?? 'Users';
@endphp

@section('title', $title)
@section('page-title', 'Manage Users')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">

        <!-- Header -->
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-800">{{ $title }}</h3>
                <p class="text-sm text-gray-600 mt-1">Manage system users and their roles</p>
            </div>

            <a href="{{ route('users.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add User
            </a>
        </div>

        <!-- Role Tabs -->
        <div class="flex gap-2 border-b border-gray-200 px-6 pt-4">
            @foreach ($roleTitles as $key => $label)
                <a href="{{ route('users.index', ['role' => $key]) }}"
                    class="{{ $role === $key ? 'border-purple-600 text-purple-700' : 'border-transparent text-gray-500 hover:text-gray-700' }} border-b-2 px-3 pb-3 text-sm font-semibold transition">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @include('users._table', ['users' => $users, 'title' => $title, 'tableId' => 'usersTable'])

    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            pageLength: 10,
            order: [[4, 'desc']], // Sort by registered date
            columnDefs: [
                { orderable: false, targets: [3, 5] } // Disable sorting on Permissions and Actions
            ],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ per page",
                info: "Showing _START_ to _END_ of _TOTAL_",
                infoEmpty: "No users found",
                infoFiltered: "(filtered from _MAX_ total)",
                zeroRecords: "No matching users found"
            }
        });
    });
</script>
@endpush
