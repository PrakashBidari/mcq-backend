@extends('layouts.dashboard')

@section('title', 'Categories')
@section('page-title', 'Manage Categories')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">

        <!-- Header -->
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Quiz Categories</h3>
                <p class="text-sm text-gray-600 mt-1">Manage quiz and book categories</p>
            </div>

            <button
                onclick="openCreateModal()"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Category
            </button>
        </div>

        <!-- Categories Table -->
        <div class="p-6">
            <table id="categoriesTable" class="w-full">
                <thead>
                    <tr>
                        <th class="text-left">Category</th>
                        <th class="text-left">Icon</th>
                        <th class="text-left">Color</th>
                        <th class="text-left">Question Sets</th>
                        <th class="text-left">Books</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl flex items-center justify-center"
                                        style="background-color: {{ $category->color }}15;"
                                    >
                                        <i class="icon ion-md-{{ $category->icon }}" style="font-size: 24px; color: {{ $category->color }};"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $category->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $category->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 text-gray-600 text-sm">
                                {{ $category->icon }}
                            </td>
                            <td class="py-4">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 rounded-lg border-2 border-gray-200"
                                        style="background-color: {{ $category->color }};"
                                    ></div>
                                    <span class="text-sm text-gray-600">{{ $category->color }}</span>
                                </div>
                            </td>
                            <td class="py-4 text-gray-600 text-sm">
                                {{ $category->question_sets_count }}
                            </td>
                            <td class="py-4 text-gray-600 text-sm">
                                {{ $category->books_count }}
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        onclick='openEditModal(@json($category))'
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>

                                    <form action="{{ route('categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure? This category has {{ $category->question_sets_count }} question sets and {{ $category->books_count }} books.');">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                                            @if($category->question_sets_count > 0 || $category->books_count > 0) disabled title="Cannot delete category with content" @endif
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl w-full max-w-md mx-4">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Create Category</h3>
            </div>

            <form action="{{ route('categories.store') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Name</label>
                    <input
                        type="text"
                        name="name"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="e.g., Science"
                    >
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Icon (Ionicon name)</label>
                    <input
                        type="text"
                        name="icon"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="e.g., flask"
                    >
                    <p class="text-xs text-gray-500 mt-1">Browse icons at: <a href="https://ionic.io/ionicons" target="_blank" class="text-purple-600">ionicons</a></p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Color</label>
                    <input
                        type="color"
                        name="color"
                        required
                        value="#667eea"
                        class="w-full h-12 border border-gray-300 rounded-lg cursor-pointer"
                    >
                </div>

                <div class="flex gap-3 pt-4">
                    <button
                        type="submit"
                        class="flex-1 px-4 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition"
                    >
                        Create
                    </button>
                    <button
                        type="button"
                        onclick="closeCreateModal()"
                        class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl w-full max-w-md mx-4">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Edit Category</h3>
            </div>

            <form id="editForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Name</label>
                    <input
                        type="text"
                        name="name"
                        id="edit_name"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    >
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Icon (Ionicon name)</label>
                    <input
                        type="text"
                        name="icon"
                        id="edit_icon"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    >
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Color</label>
                    <input
                        type="color"
                        name="color"
                        id="edit_color"
                        required
                        class="w-full h-12 border border-gray-300 rounded-lg cursor-pointer"
                    >
                </div>

                <div class="flex gap-3 pt-4">
                    <button
                        type="submit"
                        class="flex-1 px-4 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition"
                    >
                        Update
                    </button>
                    <button
                        type="button"
                        onclick="closeEditModal()"
                        class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#categoriesTable').DataTable({
            pageLength: 10,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [5] }
            ]
        });
    });

    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.getElementById('createModal').classList.add('flex');
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.getElementById('createModal').classList.remove('flex');
    }

    function openEditModal(category) {
        document.getElementById('edit_name').value = category.name;
        document.getElementById('edit_icon').value = category.icon;
        document.getElementById('edit_color').value = category.color;
        document.getElementById('editForm').action = `/dashboard/categories/${category.id}`;

        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    }

    // Close modals on outside click
    document.getElementById('createModal').addEventListener('click', function(e) {
        if (e.target === this) closeCreateModal();
    });

    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
</script>
@endpush
