@extends('layouts.dashboard')

@section('title', 'Categories')
@section('page-title', 'Manage Categories')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">

        <!-- Header -->
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-800">All Categories</h3>
                <p class="text-sm text-gray-600 mt-1">Manage quiz categories and their settings</p>
            </div>

            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'Category'))
                <a href="{{ route('categories.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Category
                </a>
            @endif
        </div>

        <!-- Table -->
        <div class="p-6">
            <table id="categoriesTable" class="w-full">
                <thead>
                    <tr>
                        <th class="text-left">Icon</th>
                        <th class="text-left">Name</th>
                        <th class="text-left">Slug</th>
                        <th class="text-left">Description</th>
                        <th class="text-left">Question Sets</th>
                        <th class="text-left">Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: {{ $category->color }}15;">
                                    <ion-icon name="{{ $category->icon ?? 'help-circle' }}" style="font-size: 24px; color: {{ $category->color }};"></ion-icon>
                                </div>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $category->color }};"></div>
                                    <span class="font-semibold text-gray-800">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td class="py-4">
                                <code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $category->slug }}</code>
                            </td>
                            <td class="py-4 text-gray-600 text-sm">
                                {{ Str::limit($category->description, 50) }}
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                                    {{ $category->question_sets_count }} sets
                                </span>
                            </td>
                            <td class="py-4 text-gray-600 text-sm">
                                {{ $category->created_at->format('M d, Y') }}
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'Category'))
                                        <a href="{{ route('categories.edit', $category->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                    @endif

                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('delete', 'Category'))
                                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category? This will also delete all associated question sets and questions.');">
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
<!-- Ionicons -->
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<script>
    $(document).ready(function() {
        $('#categoriesTable').DataTable({
            pageLength: 10,
            order: [[5, 'desc']], // Sort by created date
            columnDefs: [
                { orderable: false, targets: [0, 6] } // Disable sorting on Icon and Actions
            ],
            language: {
                search: "Search categories:",
                lengthMenu: "Show _MENU_ categories per page",
                info: "Showing _START_ to _END_ of _TOTAL_ categories",
                infoEmpty: "No categories found",
                infoFiltered: "(filtered from _MAX_ total categories)",
                zeroRecords: "No matching categories found"
            }
        });
    });
</script>
@endpush
