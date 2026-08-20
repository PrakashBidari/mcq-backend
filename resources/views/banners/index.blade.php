@extends('layouts.dashboard')

@section('title', 'Banners')
@section('page-title', 'Manage Banners')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">

        <!-- Header -->
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Home Banners</h3>
                <p class="text-sm text-gray-600 mt-1">Slides shown in the home-page hero swiper</p>
            </div>

            @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'Banner'))
                <a href="{{ route('banners.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Banner
                </a>
            @endif
        </div>

        <!-- Table -->
        <div class="p-6 overflow-x-auto">
            <table id="bannersTable" class="w-full min-w-max">
                <thead>
                    <tr>
                        <th class="text-left whitespace-nowrap">Order</th>
                        <th class="text-left whitespace-nowrap">Banner</th>
                        <th class="text-left whitespace-nowrap">Link Type</th>
                        <th class="text-left whitespace-nowrap">Status</th>
                        <th class="text-center whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($banners as $banner)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 text-gray-600 text-sm">{{ $banner->sort_order }}</td>
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $banner->display_image ?? 'https://via.placeholder.com/400x300?text=No+Image' }}"
                                        alt="{{ $banner->title }}"
                                        class="w-16 h-12 object-cover rounded-lg shadow-sm"
                                        onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'">
                                    <div class="max-w-md">
                                        <p class="font-semibold text-gray-800">{{ Str::limit($banner->title, 50) }}</p>
                                        @if ($banner->subtitle)
                                            <p class="text-xs text-gray-500">{{ Str::limit($banner->subtitle, 60) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold capitalize">
                                    {{ $banner->link_type }}
                                </span>
                            </td>
                            <td class="py-4">
                                @if ($banner->is_active)
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Active</span>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-semibold">Inactive</span>
                                @endif
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'Banner'))
                                        <a href="{{ route('banners.edit', $banner->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                    @endif

                                    @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('delete', 'Banner'))
                                        <form action="{{ route('banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Delete this banner?');">
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
        $('#bannersTable').DataTable({
            pageLength: 10,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [4] }
            ],
            language: {
                search: "Search banners:",
                lengthMenu: "Show _MENU_ banners per page",
                info: "Showing _START_ to _END_ of _TOTAL_ banners",
                infoEmpty: "No banners found",
                infoFiltered: "(filtered from _MAX_ total banners)",
                zeroRecords: "No matching banners found"
            }
        });
    });
</script>
@endpush
