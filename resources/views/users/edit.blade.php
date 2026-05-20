@extends('layouts.dashboard')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
    <div class="max-w-3xl">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('users.index') }}" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Users
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Edit User</h3>
                <p class="text-sm text-gray-600 mt-1">Update user information and role</p>
            </div>

            <form action="{{ route('users.update', $user->id) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="John Doe"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="john@example.com"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-semibold text-gray-700 mb-2">
                        User Role <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="relative">
                            <input
                                type="radio"
                                name="role"
                                value="user"
                                {{ old('role', $user->role) == 'user' ? 'checked' : '' }}
                                class="peer sr-only"
                            >
                            <div class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer transition peer-checked:border-gray-500 peer-checked:bg-gray-50 hover:border-gray-300">
                                <svg class="w-8 h-8 text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="font-semibold text-gray-700">User</span>
                                <span class="text-xs text-gray-500 mt-1">Mobile App Only</span>
                            </div>
                        </label>

                        <label class="relative">
                            <input
                                type="radio"
                                name="role"
                                value="teacher"
                                {{ old('role', $user->role) == 'teacher' ? 'checked' : '' }}
                                class="peer sr-only"
                            >
                            <div class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer transition peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300">
                                <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                <span class="font-semibold text-blue-700">Teacher</span>
                                <span class="text-xs text-blue-500 mt-1">Limited Access</span>
                            </div>
                        </label>

                        <label class="relative">
                            <input
                                type="radio"
                                name="role"
                                value="admin"
                                {{ old('role', $user->role) == 'admin' ? 'checked' : '' }}
                                class="peer sr-only"
                            >
                            <div class="flex flex-col items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer transition peer-checked:border-red-500 peer-checked:bg-red-50 hover:border-red-300">
                                <svg class="w-8 h-8 text-red-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <span class="font-semibold text-red-700">Admin</span>
                                <span class="text-xs text-red-500 mt-1">Full Access</span>
                            </div>
                        </label>
                    </div>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Change Password Section -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Change Password (Optional)</h4>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            New Password
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Leave blank to keep current password"
                        >
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                            Confirm New Password
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Confirm new password"
                        >
                    </div>
                </div>

                <!-- Stats -->
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-blue-600 font-semibold mb-1">Account Created</p>
                            <p class="text-sm text-blue-800">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                        @if($user->role === 'teacher')
                            <div>
                                <p class="text-xs text-blue-600 font-semibold mb-1">Permissions</p>
                                <a href="{{ route('permissions.edit', $user->id) }}" class="text-sm text-blue-700 hover:text-blue-900 font-semibold underline">
                                    {{ $user->teacher_permissions_count }} assigned
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center gap-3 pt-6 border-t border-gray-200">
                    <button
                        type="submit"
                        class="px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition"
                    >
                        Update User
                    </button>
                    <a
                        href="{{ route('users.index') }}"
                        class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition"
                    >
                        Cancel
                    </a>
                </div>

            </form>
        </div>

    </div>
@endsection
