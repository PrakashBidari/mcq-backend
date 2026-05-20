@extends('layouts.dashboard')

@section('title', 'Contact Settings')
@section('page-title', 'Contact Settings')

@section('content')
    <div class="rounded-lg bg-white shadow-sm">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 p-6">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Contact Settings</h3>
                <p class="mt-1 text-sm text-gray-600">Manage contact page information</p>
            </div>

            @if(!$setting)
                @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'ContactSetting'))
                    <a href="{{ route('contact-settings.create') }}" class="flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-white transition hover:bg-purple-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Contact Settings
                    </a>
                @endif
            @else
                @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('update', 'ContactSetting'))
                    <a href="{{ route('contact-settings.edit', $setting->id) }}" class="flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-white transition hover:bg-purple-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Settings
                    </a>
                @endif
            @endif
        </div>

        @if($setting)
            <!-- Settings Display -->
            <div class="p-6">
                <div class="grid gap-6 md:grid-cols-2">

                    <!-- Hero Section Card -->
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-5">
                        <div class="mb-3 flex items-center gap-2">
                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                            <h4 class="font-bold text-gray-800">Hero Section</h4>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs font-semibold text-gray-500">Title</p>
                                <p class="text-sm text-gray-800">{{ $setting->hero_title }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500">Subtitle</p>
                                <p class="text-sm text-gray-800">{{ $setting->hero_subtitle }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info Card -->
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-5">
                        <div class="mb-3 flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <h4 class="font-bold text-gray-800">Contact Information</h4>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500">Email</p>
                                    <p class="text-sm text-gray-800">{{ $setting->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500">Phone</p>
                                    <p class="text-sm text-gray-800">{{ $setting->phone }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500">Address</p>
                                    <p class="text-sm text-gray-800">{{ $setting->address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Section Card -->
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-5 md:col-span-2">
                        <div class="mb-3 flex items-center gap-2">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h4 class="font-bold text-gray-800">Form Section</h4>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold text-gray-500">Form Title</p>
                                <p class="text-sm text-gray-800">{{ $setting->form_title }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500">Form Subtitle</p>
                                <p class="text-sm text-gray-800">{{ $setting->form_subtitle }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @else
            <!-- No Settings -->
            <div class="p-12 text-center">
                <svg class="mx-auto mb-4 h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <p class="mb-4 text-gray-600">No contact settings found. Create settings to configure your contact page.</p>
                @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('create', 'ContactSetting'))
                    <a href="{{ route('contact-settings.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-purple-600 px-6 py-3 text-white transition hover:bg-purple-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create Settings
                    </a>
                @endif
            </div>
        @endif

    </div>
@endsection
