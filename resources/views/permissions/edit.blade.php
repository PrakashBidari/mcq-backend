@extends('layouts.dashboard')

@section('title', 'Edit Permissions')
@section('page-title', 'Edit Teacher Permissions')

@section('content')
    <div class="max-w-4xl">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('permissions.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Permissions
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-sm">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="text-blue-700 font-bold text-xl">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-600">{{ $user->email }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('permissions.update', $user->id) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    @foreach($availableModels as $modelKey => $modelName)
                        @php
                            $permission = $currentPermissions->get($modelKey);
                            $isEnabled = $permission !== null;
                        @endphp

                        <div class="border-2 rounded-xl overflow-hidden {{ $isEnabled ? 'border-purple-300 bg-purple-50' : 'border-gray-200' }}">
                            <!-- Model Header -->
                            <div class="p-4 bg-white border-b {{ $isEnabled ? 'border-purple-200' : 'border-gray-200' }}">
                                <label class="flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="models[{{ $modelKey }}][enabled]"
                                        {{ $isEnabled ? 'checked' : '' }}
                                        class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500 model-toggle"
                                        data-model="{{ $modelKey }}"
                                    >
                                    <span class="ml-3 text-lg font-bold text-gray-800">{{ $modelName }}</span>
                                </label>
                            </div>

                            <!-- Permissions Grid -->
                            <div class="p-5 permission-options" data-model="{{ $modelKey }}" style="{{ $isEnabled ? '' : 'display: none;' }}">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <!-- Create -->
                                    <label class="flex items-center p-3 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-300 transition">
                                        <input
                                            type="checkbox"
                                            name="models[{{ $modelKey }}][create]"
                                            {{ $permission && $permission->can_create ? 'checked' : '' }}
                                            class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                                        >
                                        <div class="ml-3">
                                            <p class="text-sm font-semibold text-gray-700">Create</p>
                                            <p class="text-xs text-gray-500">Add new</p>
                                        </div>
                                    </label>

                                    <!-- Read -->
                                    <label class="flex items-center p-3 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition">
                                        <input
                                            type="checkbox"
                                            name="models[{{ $modelKey }}][read]"
                                            {{ $permission && $permission->can_read ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        >
                                        <div class="ml-3">
                                            <p class="text-sm font-semibold text-gray-700">View</p>
                                            <p class="text-xs text-gray-500">Read only</p>
                                        </div>
                                    </label>

                                    <!-- Update -->
                                    <label class="flex items-center p-3 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:border-yellow-300 transition">
                                        <input
                                            type="checkbox"
                                            name="models[{{ $modelKey }}][update]"
                                            {{ $permission && $permission->can_update ? 'checked' : '' }}
                                            class="w-4 h-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500"
                                        >
                                        <div class="ml-3">
                                            <p class="text-sm font-semibold text-gray-700">Update</p>
                                            <p class="text-xs text-gray-500">Edit items</p>
                                        </div>
                                    </label>

                                    <!-- Delete -->
                                    <label class="flex items-center p-3 bg-white border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-300 transition">
                                        <input
                                            type="checkbox"
                                            name="models[{{ $modelKey }}][delete]"
                                            {{ $permission && $permission->can_delete ? 'checked' : '' }}
                                            class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
                                        >
                                        <div class="ml-3">
                                            <p class="text-sm font-semibold text-gray-700">Delete</p>
                                            <p class="text-xs text-gray-500">Remove items</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center gap-3 mt-8 pt-6 border-t border-gray-200">
                    <button
                        type="submit"
                        class="px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition"
                    >
                        Save Permissions
                    </button>
                    <a
                        href="{{ route('permissions.index') }}"
                        class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition"
                    >
                        Cancel
                    </a>
                </div>

            </form>
        </div>

    </div>
@endsection

@push('scripts')
<script>
    // Toggle permission options when model checkbox is clicked
    document.querySelectorAll('.model-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const model = this.dataset.model;
            const options = document.querySelector(`.permission-options[data-model="${model}"]`);

            if (this.checked) {
                options.style.display = 'block';
            } else {
                options.style.display = 'none';
                // Uncheck all permissions for this model
                options.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                    cb.checked = false;
                });
            }
        });
    });
</script>
@endpush
