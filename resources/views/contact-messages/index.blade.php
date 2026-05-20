@extends('layouts.dashboard')

@section('title', 'Contact Messages')
@section('page-title', 'Contact Messages')

@section('content')
    <div class="rounded-lg bg-white shadow-sm">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 p-6">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Contact Messages</h3>
                <p class="mt-1 text-sm text-gray-600">Manage customer inquiries and support requests</p>
            </div>

            @if($unreadCount > 0)
                <div class="rounded-full bg-red-100 px-4 py-2">
                    <span class="text-sm font-bold text-red-600">{{ $unreadCount }} Unread</span>
                </div>
            @endif
        </div>

        <!-- Filters -->
        <div class="border-b border-gray-200 bg-gray-50 p-6">
            <form method="GET" action="{{ route('contact-messages.index') }}" class="flex gap-3">
                <!-- Search -->
                <input
                    type="text"
                    name="search"
                    placeholder="Search messages..."
                    value="{{ request('search') }}"
                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                >

                <!-- Status Filter -->
                <select
                    name="status"
                    class="rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-purple-500"
                >
                    <option value="">All Status</option>
                    <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread</option>
                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                    <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>Replied</option>
                </select>

                <button type="submit" class="rounded-lg bg-purple-600 px-6 py-2 text-white transition hover:bg-purple-700">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('contact-messages.index') }}" class="rounded-lg bg-gray-200 px-6 py-2 text-gray-700 transition hover:bg-gray-300">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto p-6">
            <table id="messagesTable" class="w-full min-w-max">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap text-left">Name</th>
                        <th class="whitespace-nowrap text-left">Email</th>
                        <th class="whitespace-nowrap text-left">Message</th>
                        <th class="whitespace-nowrap text-left">Status</th>
                        <th class="whitespace-nowrap text-left">Date</th>
                        <th class="whitespace-nowrap text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 {{ $message->status === 'unread' ? 'bg-blue-50' : '' }}">
                            <td class="py-4">
                                <div class="flex items-center gap-2">
                                    @if($message->status === 'unread')
                                        <div class="h-2 w-2 rounded-full bg-blue-600"></div>
                                    @endif
                                    <p class="font-semibold text-gray-800">{{ $message->name }}</p>
                                </div>
                            </td>
                            <td class="py-4 text-gray-700">
                                {{ $message->email }}
                            </td>
                            <td class="py-4">
                                <p class="max-w-md text-sm text-gray-600">{{ Str::limit($message->message, 80) }}</p>
                            </td>
                            <td class="py-4">
                                @if($message->status === 'unread')
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                        Unread
                                    </span>
                                @elseif($message->status === 'read')
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        Read
                                    </span>
                                @else
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Replied
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 text-sm text-gray-600">
                                {{ $message->created_at->format('M d, Y') }}
                                <br>
                                <span class="text-xs text-gray-400">{{ $message->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('contact-messages.show', $message->id) }}" class="rounded-lg p-2 text-blue-600 transition hover:bg-blue-50">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>

                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('delete', 'ContactMessage'))
                                        <form action="{{ route('contact-messages.destroy', $message->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg p-2 text-red-600 transition hover:bg-red-50">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#messagesTable').DataTable({
            pageLength: 10,
            order: [[4, 'desc']], // Sort by date
            columnDefs: [
                { orderable: false, targets: [5] } // Disable sorting on Actions
            ],
            language: {
                search: "Search messages:",
                lengthMenu: "Show _MENU_ messages per page",
                info: "Showing _START_ to _END_ of _TOTAL_ messages",
                infoEmpty: "No messages found",
                infoFiltered: "(filtered from _MAX_ total messages)",
                zeroRecords: "No matching messages found"
            }
        });
    });
</script>
@endpush
