@extends('layouts.dashboard')

@section('title', 'FAQs')
@section('page-title', 'Manage FAQs')

@section('content')
    <div class="rounded-lg bg-white shadow-sm">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 p-6">
            <div>
                <h3 class="text-xl font-bold text-gray-800">All FAQs</h3>
                <p class="mt-1 text-sm text-gray-600">Manage frequently asked questions</p>
            </div>

            @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'Faq'))
                <a href="{{ route('faqs.create') }}"
                    class="flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-white transition hover:bg-purple-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add FAQ
                </a>
            @endif
        </div>

        <!-- Filters -->
        <div class="border-b border-gray-200 bg-gray-50 p-6">
            <form method="GET" action="{{ route('faqs.index') }}" class="flex gap-3">
                <!-- Search -->
                <input type="text" name="search" placeholder="Search FAQs..." value="{{ request('search') }}"
                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-purple-500">

                <!-- Category Filter -->
                <select name="category"
                    class="rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="rounded-lg bg-purple-600 px-6 py-2 text-white transition hover:bg-purple-700">
                    Filter
                </button>

                @if (request()->hasAny(['search', 'category']))
                    <a href="{{ route('faqs.index') }}"
                        class="rounded-lg bg-gray-200 px-6 py-2 text-gray-700 transition hover:bg-gray-300">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto p-6">
            <table id="faqsTable" class="w-full min-w-max">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap text-left">Order</th>
                        <th class="whitespace-nowrap text-left">Question</th>
                        <th class="whitespace-nowrap text-left">Category</th>
                        <th class="whitespace-nowrap text-left">Status</th>
                        <th class="whitespace-nowrap text-left">Created</th>
                        <th class="whitespace-nowrap text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($faqs as $faq)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4">
                                <span class="rounded-lg bg-gray-100 px-3 py-1 text-sm font-bold text-gray-700">
                                    {{ $faq->order }}
                                </span>
                            </td>
                            <td class="py-4">
                                <div class="max-w-md">
                                    <p class="font-semibold text-gray-800">{{ Str::limit($faq->question, 80) }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ Str::limit($faq->answer, 100) }}</p>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-700">
                                    {{ $faq->category->name }}
                                </span>
                            </td>
                            <td class="py-4">
                                @if ($faq->is_active)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 text-sm text-gray-600">
                                {{ $faq->created_at->format('M d, Y') }}
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'Faq'))
                                        <a href="{{ route('faqs.edit', $faq->id) }}"
                                            class="rounded-lg p-2 text-blue-600 transition hover:bg-blue-50">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                    @endif

                                    @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('delete', 'Faq'))
                                        <form action="{{ route('faqs.destroy', $faq->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this FAQ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg p-2 text-red-600 transition hover:bg-red-50">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
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
        $(document).ready(function() {
            $('#faqsTable').DataTable({
                pageLength: 10,
                order: [
                    [0, 'asc']
                ], // Sort by order
                columnDefs: [{
                        orderable: false,
                        targets: [5]
                    } // Disable sorting on Actions
                ],
                language: {
                    search: "Search FAQs:",
                    lengthMenu: "Show _MENU_ FAQs per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ FAQs",
                    infoEmpty: "No FAQs found",
                    infoFiltered: "(filtered from _MAX_ total FAQs)",
                    zeroRecords: "No matching FAQs found"
                }
            });
        });
    </script>
@endpush
