@extends('layouts.dashboard')

@section('title', 'Teacher Permissions')
@section('page-title', 'Manage Teacher Permissions')

@section('content')
    <div class="bg-white rounded-lg shadow-sm">

        <!-- Header -->
        <div class="p-6 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Teacher Permissions</h3>
                <p class="text-sm text-gray-600 mt-1">Manage what teachers can access in the dashboard</p>
            </div>
        </div>

        <!-- Teachers List -->
        <div class="p-6">
            @if($teachers->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($teachers as $teacher)
                        <div class="bg-white border-2 border-gray-200 rounded-xl p-5 hover:border-purple-300 transition">
                            <!-- Teacher Info -->
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-700 font-bold text-lg">
                                        {{ strtoupper(substr($teacher->name, 0, 2)) }}
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-gray-800">{{ $teacher->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $teacher->email }}</p>
                                </div>
                            </div>

                            <!-- Permission Count -->
                            <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-blue-700 font-semibold">Permissions Assigned</span>
                                    <span class="text-2xl font-bold text-blue-600">{{ $teacher->teacher_permissions_count }}</span>
                                </div>
                            </div>

                            <!-- Manage Button -->
                            <a href="{{ route('permissions.edit', $teacher->id) }}" class="block w-full py-2.5 bg-purple-600 text-white text-center font-semibold rounded-lg hover:bg-purple-700 transition">
                                Manage Permissions
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">No Teachers Found</h3>
                    <p class="text-gray-500 mb-4">Create users with "Teacher" role to manage their permissions</p>
                    <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Teacher
                    </a>
                </div>
            @endif
        </div>

    </div>
@endsection
