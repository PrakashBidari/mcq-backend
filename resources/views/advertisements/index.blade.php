@extends('layouts.dashboard')

@section('title', 'Advertise')
@section('page-title', 'Manage Advertisements')

@section('content')
    <div class="rounded-lg bg-white shadow-sm">

        <!-- Header -->
        <div class="border-b border-gray-200 p-6">
            <h3 class="text-xl font-bold text-gray-800">All Advertisements</h3>
            <p class="mt-1 text-sm text-gray-600">
                Manage the sponsored ad slots shown on the mobile app home screen. Slots are fixed — content and
                visibility can be edited, but slots cannot be added or removed.
            </p>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto p-6">
            <table id="advertisementsTable" class="w-full min-w-max">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap text-left">Slot</th>
                        <th class="whitespace-nowrap text-left">Title</th>
                        <th class="whitespace-nowrap text-left">Description</th>
                        <th class="whitespace-nowrap text-left">Status</th>
                        <th class="whitespace-nowrap text-left">Updated</th>
                        <th class="whitespace-nowrap text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($advertisements as $ad)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4">
                                <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-700">
                                    {{ ucfirst($ad->position) }}
                                </span>
                            </td>
                            <td class="py-4">
                                <p class="max-w-xs font-semibold text-gray-800">{{ Str::limit($ad->title, 60) }}</p>
                            </td>
                            <td class="py-4">
                                <p class="max-w-md text-xs text-gray-500">{{ Str::limit($ad->description, 100) }}</p>
                            </td>
                            <td class="py-4">
                                @if ($ad->is_active)
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
                                {{ $ad->updated_at->format('M d, Y') }}
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'Advertise'))
                                        <a href="{{ route('advertisements.edit', $ad->id) }}"
                                            class="rounded-lg p-2 text-blue-600 transition hover:bg-blue-50" title="Edit">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </a>

                                        <form action="{{ route('advertisements.toggle', $ad->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <label class="relative inline-flex cursor-pointer items-center"
                                                title="{{ $ad->is_active ? 'Deactivate' : 'Activate' }}">
                                                <input type="checkbox" class="peer sr-only"
                                                    {{ $ad->is_active ? 'checked' : '' }}
                                                    onchange="this.form.submit()">
                                                <div
                                                    class="h-6 w-11 rounded-full bg-gray-300 transition-colors after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-green-500 peer-checked:after:translate-x-full peer-checked:after:border-white">
                                                </div>
                                            </label>
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
            $('#advertisementsTable').DataTable({
                pageLength: 10,
                order: [
                    [0, 'asc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [5]
                }],
                language: {
                    search: "Search Advertisements:",
                    lengthMenu: "Show _MENU_ per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ advertisements",
                    infoEmpty: "No advertisements found",
                    infoFiltered: "(filtered from _MAX_ total advertisements)",
                    zeroRecords: "No matching advertisements found"
                }
            });
        });
    </script>
@endpush
