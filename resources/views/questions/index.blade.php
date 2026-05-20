@extends('layouts.dashboard')

@section('title', 'Questions')
@section('page-title', 'Manage Questions')

@section('content')
    <div class="rounded-lg bg-white shadow-sm">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 p-6">
            <div>
                <h3 class="text-xl font-bold text-gray-800">All Questions</h3>
                <p class="mt-1 text-sm text-gray-600">Manage quiz questions across all sets</p>
            </div>

            @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'Question'))
                <a href="{{ route('questions.create') }}"
                    class="flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-white transition hover:bg-purple-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Question
                </a>
            @endif
        </div>

        <!-- Filters -->
        <div class="border-b border-gray-200 bg-gray-50 p-6">
            <form method="GET" action="{{ route('questions.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <!-- Category Filter -->
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-700">Category</label>
                    <select name="category" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Question Set Filter -->
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-700">Question Set</label>
                    <select name="question_set" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">All Sets</option>
                        @foreach ($questionSets as $set)
                            <option value="{{ $set->id }}" {{ request('question_set') == $set->id ? 'selected' : '' }}>
                                {{ $set->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Difficulty Filter -->
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-700">Difficulty</label>
                    <select name="difficulty" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">All Levels</option>
                        <option value="Easy" {{ request('difficulty') == 'Easy' ? 'selected' : '' }}>Easy</option>
                        <option value="Medium" {{ request('difficulty') == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="Hard" {{ request('difficulty') == 'Hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>

                <!-- Filter Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="rounded-lg bg-purple-600 px-4 py-2 text-sm text-white transition hover:bg-purple-700">
                        Apply Filters
                    </button>
                    <a href="{{ route('questions.index') }}"
                        class="rounded-lg bg-gray-200 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-300">
                        Clear
                    </a>
                </div>
            </form>
        </div>


        <!-- Table -->
        <div class="overflow-x-auto p-6">
            <table id="questionsTable" class="w-full min-w-max">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap text-left">Question</th>
                        <th class="whitespace-nowrap text-left">Position</th>
                        <th class="whitespace-nowrap text-left">Category</th>
                        <th class="whitespace-nowrap text-left">Question Sets</th>
                        <th class="whitespace-nowrap text-left">Difficulty</th>
                        <th class="whitespace-nowrap text-left">Correct Answer</th>
                        <th class="whitespace-nowrap text-left">Created</th>
                        <th class="whitespace-nowrap text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($questions as $question)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="max-w-md py-4">
                                <p class="mb-1 font-semibold text-gray-800">{{ Str::limit($question->question, 80) }}</p>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach ($question->options as $index => $option)
                                        <span
                                            class="{{ $index == $question->correct_answer ? 'bg-green-100 text-green-700 font-semibold' : 'bg-gray-100 text-gray-600' }} rounded px-2 py-1 text-xs">
                                            {{ chr(65 + $index) }}: {{ Str::limit($option->option_text, 20) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4">
                                @if ($question->position)
                                    <span
                                        class="inline-flex items-center justify-center rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-700">
                                        # {{ $question->position ? $question->position : '*' }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-4">
                                @php
                                    // Get unique categories from question sets
                                    $categories = $question->questionSets->pluck('category')->unique();
                                @endphp
                                <div class="flex flex-col gap-1">
                                    @foreach ($categories as $category)
                                        <div class="flex items-center gap-2 whitespace-nowrap">
                                            <div class="h-3 w-3 rounded-full"
                                                style="background-color: {{ $category->color }};"></div>
                                            <span class="text-sm text-gray-700">{{ $category->name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="flex flex-col gap-1">
                                    @foreach ($question->questionSets as $set)
                                        <span
                                            class="inline-block whitespace-nowrap rounded bg-blue-50 px-2 py-1 text-xs text-blue-700">
                                            {{ $set->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <td class="py-4">
                                @php
                                    $difficultyColors = [
                                        'Easy' => 'bg-green-100 text-green-700',
                                        'Medium' => 'bg-yellow-100 text-yellow-700',
                                        'Hard' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <span
                                    class="{{ $difficultyColors[$question->difficulty] ?? 'bg-gray-100 text-gray-700' }} whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold">
                                    {{ $question->difficulty }}
                                </span>
                            </td>
                            <td class="py-4">
                                <span
                                    class="whitespace-nowrap rounded-full bg-green-50 px-3 py-1 text-sm font-semibold text-green-700">
                                    {{ chr(65 + $question->correct_answer) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap py-4 text-sm text-gray-600">
                                {{ $question->created_at->format('M d, Y') }}
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                                    @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'Question'))
                                        <a href="{{ route('questions.edit', $question->id) }}"
                                            class="rounded-lg p-2 text-blue-600 transition hover:bg-blue-50">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                    @endif

                                    @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('delete', 'Question'))
                                        <form action="{{ route('questions.destroy', $question->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this question?');">
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
            $('#questionsTable').DataTable({
                pageLength: 4,
                order: [
                    [5, 'desc']
                ], // Sort by created date
                columnDefs: [{
                        orderable: false,
                        targets: [6]
                    } // Disable sorting on Actions
                ],
                language: {
                    search: "Search questions:",
                    lengthMenu: "Show _MENU_ questions per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ questions",
                    infoEmpty: "No questions found",
                    infoFiltered: "(filtered from _MAX_ total questions)",
                    zeroRecords: "No matching questions found"
                }
            });
        });
    </script>
@endpush
