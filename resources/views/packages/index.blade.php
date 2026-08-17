@extends('layouts.dashboard')

@section('title', 'Question Set Packages')
@section('page-title', 'Question Set Packages')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-gray-800">All Packages</h3>
                <p class="text-sm text-gray-600 mt-1">Bundles of question sets sold as a single purchase</p>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'Package'))
                <a href="{{ route('packages.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Add Package
                </a>
            @endif
        </div>

        <!-- Filters -->
        <div class="border-b border-gray-200 bg-gray-50 p-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-700">Category</label>
                    <select id="categoryFilter" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-700">Subcategory</label>
                    <select id="subcategoryFilter" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                        <option value="">All Subcategories</option>
                        @foreach ($categories as $category)
                            @foreach ($category->children as $sub)
                                <option value="{{ $sub->id }}" data-parent="{{ $category->id }}">{{ $sub->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button" id="clearFilters" class="rounded-lg bg-gray-200 px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-300">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto p-6">
            <table id="packagesTable" class="w-full min-w-max">
                <thead>
                    <tr>
                        <th class="text-left py-2">Name</th>
                        <th class="text-left py-2">Category</th>
                        <th class="text-left py-2">Subcategory</th>
                        <th class="text-left py-2">Question Sets</th>
                        <th class="text-left py-2">Price</th>
                        <th class="text-left py-2">Trial</th>
                        <th class="text-left py-2">Status</th>
                        <th class="text-center py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packages as $package)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 font-semibold text-gray-800">{{ $package->name }}</td>
                            <td class="py-4 text-sm text-gray-600" data-id="{{ $package->category_id }}">
                                {{ $package->category->name ?? '—' }}
                            </td>
                            <td class="py-4 text-sm text-gray-600" data-id="{{ $package->subcategory_id }}">
                                {{ $package->subcategory->name ?? '—' }}
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">{{ $package->question_sets_count }} sets</span>
                            </td>
                            <td class="py-4 text-sm text-gray-600">
                                @if ($package->is_paid)
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-gray-800">&yen;{{ number_format($package->priceTier->amount ?? 0, 2) }}</span>
                                        @if ($package->access_type)
                                            <span class="text-xs text-gray-500">{{ $package->access_value }} {{ $package->access_type }}</span>
                                        @endif
                                    </div>
                                @else
                                    Free
                                @endif
                            </td>
                            <td class="py-4 text-sm text-gray-600">
                                {{ $package->trial_enabled ? $package->trial_value . ' ' . $package->trial_type : '—' }}
                            </td>
                            <td class="py-4">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $package->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $package->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center justify-center gap-2">
                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'Package'))
                                        <a href="{{ route('packages.edit', $package->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">Edit</a>
                                    @endif
                                    @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('delete', 'Package'))
                                        <form action="{{ route('packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('Delete this package? Its question sets will be kept and moved back to single browsing.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">Delete</button>
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
            var table = $('#packagesTable').DataTable({
                pageLength: 10,
                order: [
                    [0, 'asc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [7]
                }],
                language: {
                    search: "Search packages:",
                    lengthMenu: "Show _MENU_ packages per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ packages",
                    infoEmpty: "No packages found",
                    infoFiltered: "(filtered from _MAX_ total packages)",
                    zeroRecords: "No matching packages found"
                }
            });

            var categoryFilter = document.getElementById('categoryFilter');
            var subcategoryFilter = document.getElementById('subcategoryFilter');
            var subcategoryOptions = Array.from(subcategoryFilter.options);

            function applyFilters() {
                var catId = categoryFilter.value;
                var subId = subcategoryFilter.value;

                $.fn.dataTable.ext.search.push(function(settings, data, rowIndex) {
                    if (settings.nTable.id !== 'packagesTable') return true;

                    var row = table.row(rowIndex).node();
                    if (catId && $(row).find('td').eq(1).data('id') != catId) return false;
                    if (subId && $(row).find('td').eq(2).data('id') != subId) return false;
                    return true;
                });

                table.draw();
                $.fn.dataTable.ext.search.pop();
            }

            categoryFilter.addEventListener('change', function() {
                var catId = this.value;

                // Rebuild the subcategory dropdown to only show children of the chosen category
                subcategoryFilter.innerHTML = '';
                subcategoryOptions.forEach(function(opt) {
                    if (!catId || opt.value === '' || opt.dataset.parent === catId) {
                        subcategoryFilter.appendChild(opt.cloneNode(true));
                    }
                });
                subcategoryFilter.value = '';

                applyFilters();
            });

            subcategoryFilter.addEventListener('change', applyFilters);

            document.getElementById('clearFilters').addEventListener('click', function() {
                categoryFilter.value = '';
                subcategoryFilter.innerHTML = '';
                subcategoryOptions.forEach(function(opt) {
                    subcategoryFilter.appendChild(opt.cloneNode(true));
                });
                subcategoryFilter.value = '';
                applyFilters();
            });
        });
    </script>
@endpush
