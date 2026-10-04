@extends('layouts.dashboard')

@section('title', 'Paragraphs')
@section('page-title', 'Manage Paragraphs')

@section('content')
    <div class="mb-6">
        <a href="{{ route('questions.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Questions
        </a>
    </div>

    <div class="rounded-lg bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-200 p-6">
            <div>
                <h3 class="text-xl font-bold text-gray-800">All Paragraphs</h3>
                <p class="mt-1 text-sm text-gray-600">
                    A paragraph is shown in the app together with all of its questions on one page. Each question is
                    still worth 1 mark.
                </p>
            </div>

            @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'Question'))
                <a href="{{ route('paragraphs.create') }}"
                    class="flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-white transition hover:bg-purple-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Paragraph
                </a>
            @endif
        </div>

        <div class="overflow-x-auto p-6">
            <table id="paragraphsTable" class="w-full min-w-max">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap text-left">#</th>
                        <th class="whitespace-nowrap text-left">Paragraph</th>
                        <th class="whitespace-nowrap text-left">Image</th>
                        <th class="whitespace-nowrap text-left">Questions</th>
                        <th class="whitespace-nowrap text-left">Question Sets</th>
                        <th class="whitespace-nowrap text-left">Created</th>
                        <th class="whitespace-nowrap text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($paragraphs as $paragraph)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 text-sm text-gray-500">{{ $paragraph->id }}</td>
                            <td class="max-w-md py-4">
                                @if ($paragraph->title)
                                    <p class="font-semibold text-gray-800">{{ $paragraph->title }}</p>
                                @endif
                                <p class="text-sm text-gray-600">{{ Str::limit($paragraph->content, 120) }}</p>
                            </td>
                            <td class="py-4">
                                @if ($paragraph->image_url)
                                    <img src="{{ $paragraph->image_url }}" alt="" class="h-12 rounded border border-gray-200 object-contain">
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-4">
                                <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-700">
                                    {{ $paragraph->questions_count }}
                                </span>
                            </td>
                            <td class="py-4">
                                <div class="flex flex-col gap-1">
                                    @foreach ($paragraph->questions->flatMap->questionSets->unique('id') as $set)
                                        <span class="inline-block whitespace-nowrap rounded bg-blue-50 px-2 py-1 text-xs text-blue-700">{{ $set->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="whitespace-nowrap py-4 text-sm text-gray-600">
                                {{ $paragraph->created_at->format('M d, Y') }}
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                                    @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'Question'))
                                        <a href="{{ route('paragraphs.edit', $paragraph->id) }}"
                                            class="rounded-lg p-2 text-blue-600 transition hover:bg-blue-50" title="Edit paragraph & questions">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                    @endif
                                    @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('delete', 'Question'))
                                        <form action="{{ route('paragraphs.destroy', $paragraph->id) }}" method="POST"
                                            onsubmit="return confirm('Delete this paragraph and all of its questions?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg p-2 text-red-600 transition hover:bg-red-50">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            $('#paragraphsTable').DataTable({
                pageLength: 10,
                order: [[0, 'desc']],
                columnDefs: [{ orderable: false, targets: [2, 4, 6] }],
                language: {
                    search: "Search paragraphs:",
                    emptyTable: "No paragraphs yet",
                    zeroRecords: "No matching paragraphs found"
                }
            });
        });
    </script>
@endpush
