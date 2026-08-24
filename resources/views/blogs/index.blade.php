@extends('layouts.dashboard')

@section('title', 'Blogs')
@section('page-title', 'Manage Blogs')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">

        <!-- Header -->
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-800">All Blog Posts</h3>
                <p class="text-sm text-gray-600 mt-1">Manage your blog articles</p>
            </div>

            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'Blog'))
                <a href="{{ route('blogs.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Blog
                </a>
            @endif
        </div>

        <!-- Filters -->
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <form method="GET" action="{{ route('blogs.index') }}" class="flex gap-3">
                <!-- Search -->
                <input
                    type="text"
                    name="search"
                    placeholder="Search blogs..."
                    value="{{ request('search') }}"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                >

                <!-- Category Filter -->
                <select
                    name="category"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                >
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'category']))
                    <a href="{{ route('blogs.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="p-6 overflow-x-auto">
            <table id="blogsTable" class="w-full min-w-max">
                <thead>
                    <tr>
                        <th class="text-left whitespace-nowrap">Title</th>
                        <th class="text-left whitespace-nowrap">Category</th>
                        <th class="text-left whitespace-nowrap">Author</th>
                        <th class="text-left whitespace-nowrap">Read Time</th>
                        <th class="text-left whitespace-nowrap">Likes</th>
                        <th class="text-left whitespace-nowrap">Views</th>
                        <th class="text-left whitespace-nowrap">Status</th>
                        <th class="text-left whitespace-nowrap">Published</th>
                        <th class="text-center whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($blogs as $blog)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    @php
                                        $imageUrl = $blog->image
                                            ? asset('storage/' . $blog->image)
                                            : ($blog->cover_url ?? 'https://via.placeholder.com/400x300?text=No+Image');
                                    @endphp
                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $blog->title }}"
                                        class="w-16 h-12 object-cover rounded-lg shadow-sm"
                                        onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'"
                                    >
                                    <div class="max-w-md">
                                        <p class="font-semibold text-gray-800">{{ Str::limit($blog->title, 50) }}</p>
                                        <p class="text-xs text-gray-500">{{ Str::limit($blog->excerpt, 60) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                                    {{ $blog->category }}
                                </span>
                            </td>
                            <td class="py-4 text-gray-700">
                                {{ $blog->author }}
                            </td>
                            <td class="py-4 text-gray-600 text-sm">
                                {{ $blog->read_time }}
                            </td>
                            <td class="py-4 text-gray-600 text-sm">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $blog->likes }}
                                </div>
                            </td>
                            <td class="py-4 text-gray-600 text-sm">
                                {{ number_format($blog->views) }}
                            </td>
                            <td class="py-4">
                                @if($blog->is_active)
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                        Active
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 text-gray-600 text-sm">
                                {{ $blog->published_at ? $blog->published_at->format('M d, Y') : 'Draft' }}
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'Blog'))
                                        <a href="{{ route('blogs.edit', $blog->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                    @endif

                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('delete', 'Blog'))
                                        <form action="{{ route('blogs.destroy', $blog->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this blog?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
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
        $('#blogsTable').DataTable({
            pageLength: 10,
            order: [[7, 'desc']], // Sort by published date
            columnDefs: [
                { orderable: false, targets: [8] } // Disable sorting on Actions
            ],
            language: {
                search: "Search blogs:",
                lengthMenu: "Show _MENU_ blogs per page",
                info: "Showing _START_ to _END_ of _TOTAL_ blogs",
                infoEmpty: "No blogs found",
                infoFiltered: "(filtered from _MAX_ total blogs)",
                zeroRecords: "No matching blogs found"
            }
        });
    });
</script>
@endpush
