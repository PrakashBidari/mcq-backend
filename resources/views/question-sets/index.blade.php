@extends('layouts.dashboard')

@section('title', 'Question Sets')
@section('page-title', 'Manage Question Sets')

@section('content')
    <div class="rounded-lg bg-white shadow-sm">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 p-6">
            <div>
                <h3 class="text-xl font-bold text-gray-800">All Question Sets</h3>
                <p class="mt-1 text-sm text-gray-600">Manage question sets across all categories</p>
            </div>

            @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'QuestionSet'))
                <a href="{{ route('question-sets.create') }}"
                    class="flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-white transition hover:bg-purple-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Question Set
                </a>
            @endif
        </div>

        <!-- Table -->
        <div class="overflow-x-auto p-6">
            <table id="questionSetsTable" class="w-full min-w-max" >
                <thead>
                    <tr>
                        <th class="text-left">Name</th>
                        <th class="text-left">Time Limit</th>
                        <th class="text-left">Category</th>
                        <th class="text-left">Subcategory</th>
                        <th class="text-left">Description</th>
                        <th class="text-left">Questions</th>
                        <th class="text-left">Price</th>
                        <th class="text-left">Status</th>
                        <th class="text-left">Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($questionSets as $set)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4">
                                <span class="font-semibold text-gray-800">{{ $set->name }}</span>
                            </td>
                            <td class="py-4">
                                @if ($set->time_limit)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-700">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $set->time_limit }} min
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">No limit</span>
                                @endif
                            </td>
                            @php
                                $topCategory = $set->category->isSubcategory() ? $set->category->parent : $set->category;
                                $subCategory = $set->category->isSubcategory() ? $set->category : null;
                            @endphp
                            <td class="py-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-full"
                                        style="background-color: {{ $topCategory->color }};"></div>
                                    <span class="text-gray-700">{{ $topCategory->name }}</span>
                                </div>
                            </td>
                            <td class="py-4">
                                @if ($subCategory)
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-2 rounded-full"
                                            style="background-color: {{ $subCategory->color }};"></div>
                                        <span class="text-gray-700">{{ $subCategory->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-4 text-sm text-gray-600">
                                {{ Str::limit($set->description, 60) }}
                            </td>
                            <td class="py-4">
                                <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">
                                    {{ $set->questions_count }} questions
                                </span>
                            </td>
                            <td class="py-4">
                                @if ($set->is_paid)
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="inline-flex w-fit items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                            <span class="font-bold">&yen;</span>
                                            Paid
                                        </span>
                                        <span
                                            class="text-sm font-bold text-gray-700">&yen;{{ number_format($set->price, 2) }}</span>
                                        @if ($set->access_type)
                                            <span class="text-xs text-gray-500">{{ $set->access_value }} {{ $set->access_type }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span
                                        class="inline-flex w-fit items-center gap-1 rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold text-teal-700">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Free
                                    </span>
                                @endif
                            </td>
                            <td class="py-4">
                                @if ($set->is_active)
                                    <span
                                        class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Active</span>
                                @else
                                    <span
                                        class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Inactive</span>
                                @endif
                            </td>
                            <td class="py-4 text-sm text-gray-600">
                                {{ $set->created_at->format('M d, Y') }}
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'QuestionSet'))
                                        <a href="{{ route('question-sets.edit', $set->id) }}"
                                            class="rounded-lg p-2 text-blue-600 transition hover:bg-blue-50">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                    @endif

                                    @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('delete', 'QuestionSet'))
                                        <form action="{{ route('question-sets.destroy', $set->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure? This will also delete all questions in this set.');">
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
            $('#questionSetsTable').DataTable({
                pageLength: 10,
                order: [
                    [7, 'desc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [9]
                }],
                language: {
                    search: "Search question sets:",
                    lengthMenu: "Show _MENU_ sets per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ question sets",
                    infoEmpty: "No question sets found",
                    infoFiltered: "(filtered from _MAX_ total sets)",
                    zeroRecords: "No matching question sets found"
                }
            });
        });
    </script>
@endpush
