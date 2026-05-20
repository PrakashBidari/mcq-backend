@extends('layouts.dashboard')

@section('title', 'Books')
@section('page-title', 'Manage Books')

@section('content')
    <div class="rounded-lg bg-white shadow-sm">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 p-6">
            <div>
                <h3 class="text-xl font-bold text-gray-800">All Books</h3>
                <p class="mt-1 text-sm text-gray-600">Manage your study library</p>
            </div>

            @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'Book'))
                <a href="{{ route('books.create') }}"
                    class="flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-white transition hover:bg-purple-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Book
                </a>
            @endif
        </div>

        <!-- Filters -->
        <div class="border-b border-gray-200 bg-gray-50 p-6">
            <form method="GET" action="{{ route('books.index') }}" class="flex gap-3">
                <!-- Search -->
                <input type="text" name="search" placeholder="Search books or authors..."
                    value="{{ request('search') }}"
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

                <!-- Difficulty Filter -->
                <select name="difficulty"
                    class="rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-purple-500">
                    <option value="">All Levels</option>
                    <option value="Beginner" {{ request('difficulty') == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                    <option value="Intermediate" {{ request('difficulty') == 'Intermediate' ? 'selected' : '' }}>
                        Intermediate</option>
                    <option value="Advanced" {{ request('difficulty') == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                </select>

                <button type="submit" class="rounded-lg bg-purple-600 px-6 py-2 text-white transition hover:bg-purple-700">
                    Filter
                </button>

                @if (request()->hasAny(['search', 'category', 'difficulty']))
                    <a href="{{ route('books.index') }}"
                        class="rounded-lg bg-gray-200 px-6 py-2 text-gray-700 transition hover:bg-gray-300">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto p-6">
            <table id="booksTable" class="w-full min-w-max">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap text-left">Book</th>
                        <th class="whitespace-nowrap text-left">Author</th>
                        <th class="whitespace-nowrap text-left">Category</th>
                        <th class="whitespace-nowrap text-left">Difficulty</th>
                        <th class="whitespace-nowrap text-left">Rating</th>
                        <th class="whitespace-nowrap text-left">Pages</th>
                        <th class="whitespace-nowrap text-left">Duration</th>
                        <th class="whitespace-nowrap text-left">Students</th>
                        <th class="whitespace-nowrap text-left">Status</th>
                        <th class="whitespace-nowrap text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($books as $book)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    @php
                                        $imageUrl = $book->image ? asset('storage/' . $book->image) : $book->cover;
                                    @endphp
                                    <img src="{{ $imageUrl }}" alt="{{ $book->title }}"
                                        class="h-16 w-12 rounded-lg object-cover shadow-sm"
                                        onerror="this.src='https://via.placeholder.com/400x600?text=No+Cover'">
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ Str::limit($book->title, 40) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 text-gray-700">
                                {{ $book->author }}
                            </td>
                            <td class="py-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-full"
                                        style="background-color: {{ $book->category->color }};"></div>
                                    <span class="text-sm text-gray-700">{{ $book->category->name }}</span>
                                </div>
                            </td>
                            <td class="py-4">
                                @php
                                    $difficultyColors = [
                                        'Beginner' => 'bg-green-100 text-green-700',
                                        'Intermediate' => 'bg-yellow-100 text-yellow-700',
                                        'Advanced' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <span
                                    class="{{ $difficultyColors[$book->difficulty] ?? 'bg-gray-100 text-gray-700' }} rounded-full px-3 py-1 text-xs font-semibold">
                                    {{ $book->difficulty }}
                                </span>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center gap-1">
                                    <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-700">{{ $book->rating }}</span>
                                </div>
                            </td>
                            <td class="py-4 text-sm text-gray-600">
                                {{ $book->pages }} pages
                            </td>
                            <td class="py-4 text-sm text-gray-600">
                                {{ $book->duration }}
                            </td>
                            <td class="py-4 text-sm text-gray-600">
                                {{ number_format($book->students) }}
                            </td>
                            <td class="py-4">
                                @if ($book->is_active)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'Book'))
                                        <a href="{{ route('books.edit', $book->id) }}"
                                            class="rounded-lg p-2 text-blue-600 transition hover:bg-blue-50">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>
                                    @endif

                                    @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('delete', 'Book'))
                                        <form action="{{ route('books.destroy', $book->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this book?');">
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
            $('#booksTable').DataTable({
                pageLength: 10,
                order: [
                    [0, 'asc']
                ], // Sort by book title
                columnDefs: [{
                        orderable: false,
                        targets: [9]
                    } // Disable sorting on Actions
                ],
                language: {
                    search: "Search books:",
                    lengthMenu: "Show _MENU_ books per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ books",
                    infoEmpty: "No books found",
                    infoFiltered: "(filtered from _MAX_ total books)",
                    zeroRecords: "No matching books found"
                }
            });
        });
    </script>
@endpush
