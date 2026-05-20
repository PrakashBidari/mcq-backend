<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - MCQ App</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwind.min.css">

    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.tailwind.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* DataTables Custom Styling */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            @apply border border-gray-300 rounded-lg px-3 py-2 text-sm;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            @apply px-3 py-1 rounded border border-gray-300 mx-1;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            @apply bg-purple-600 text-white border-purple-600;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-50" x-data="{ sidebarOpen: true }">

    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-purple-700 to-purple-900 text-white transition-transform duration-300 ease-in-out lg:static lg:inset-0 lg:translate-x-0">
            <!-- Logo -->
            <div class="flex h-16 items-center justify-between border-b border-purple-600 px-6">
                <h1 class="text-2xl font-bold">MCQ Dashboard</h1>
                <button @click="sidebarOpen = false" class="lg:hidden">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="h-[calc(100vh-4rem)] space-y-2 overflow-y-auto px-4 py-6">

                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}"
                    class="{{ request()->routeIs('dashboard') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex items-center rounded-lg px-4 py-3 transition">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    Dashboard
                </a>

                @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'Category'))
                    <!-- Categories -->
                    <a href="{{ route('categories.index') }}"
                        class="{{ request()->routeIs('categories.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex items-center rounded-lg px-4 py-3 transition">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                            </path>
                        </svg>
                        Categories
                    </a>
                @endif

                <!-- Divider -->
                <div class="my-4 border-t border-purple-600"></div>

                @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'QuestionSet'))
                    <!-- Question Sets -->
                    <a href="{{ route('question-sets.index') }}"
                        class="{{ request()->routeIs('question-sets.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex items-center rounded-lg px-4 py-3 transition">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                        Question Sets
                    </a>
                @endif

                @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'Question'))
                    <!-- Questions -->
                    <a href="{{ route('questions.index') }}"
                        class="{{ request()->routeIs('questions.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex items-center rounded-lg px-4 py-3 transition">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                        Questions
                    </a>
                @endif



                <!-- Divider -->
                <div class="my-4 border-t border-purple-600"></div>

                <!-- Books -->
                @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'Book'))
                    <a href="{{ route('books.index') }}"
                        class="{{ request()->routeIs('books.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex items-center rounded-lg px-4 py-3 transition">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                        {{-- <span class="font-medium">Books</span> --}}
                        Books
                    </a>
                @endif


                <!-- Blogs -->
                @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'Blog'))
                    <a href="{{ route('blogs.index') }}"
                        class="{{ request()->routeIs('blogs.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex items-center gap-3 rounded-lg px-4 py-3 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z">
                            </path>
                        </svg>
                        Blogs
                        {{-- <span class="font-medium">Blogs</span> --}}
                    </a>
                @endif

                <!-- FAQs -->
                @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'Faq'))
                    <a href="{{ route('faqs.index') }}"
                        class="{{ request()->routeIs('faqs.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex items-center gap-3 rounded-lg px-4 py-3 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                        FAQs
                        {{-- <span class="font-medium">FAQs</span> --}}
                    </a>
                @endif

                <!-- Contact Us -->
                <div x-data="{ open: false }" class="space-y-1">
                    <button @click="open = !open"
                        class="{{ request()->routeIs('contact-settings.*') || request()->routeIs('contact-messages.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex w-full items-center justify-between rounded-lg px-4 py-3 transition">
                        <div class="flex items-center">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            Contact Us
                        </div>
                        <svg :class="open ? 'rotate-180' : ''" class="h-4 w-4 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>

                    <!-- Dropdown Items -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95" class="ml-8 space-y-1">
                        <a href="{{ route('contact-settings.index') }}"
                            class="{{ request()->routeIs('contact-settings.*') ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Contact Settings
                        </a>

                        <a href="{{ route('contact-messages.index') }}"
                            class="{{ request()->routeIs('contact-messages.*') ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                </path>
                            </svg>
                            Contact Messages
                        </a>
                    </div>
                </div>


                <!-- Divider -->
                <div class="my-4 border-t border-purple-600"></div>

                @if (auth()->user()->isAdmin())
                    <!-- Users (Admin Only) -->
                    <a href="{{ route('users.index') }}"
                        class="{{ request()->routeIs('users.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex items-center rounded-lg px-4 py-3 transition">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                        Users
                    </a>

                    <!-- Permissions (Admin Only) -->
                    <a href="{{ route('permissions.index') }}"
                        class="{{ request()->routeIs('permissions.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex items-center rounded-lg px-4 py-3 transition">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                        Permissions
                    </a>
                @endif

            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-1 flex-col overflow-hidden">

            @if ($errors->any())
                <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                    <strong>Whoops!</strong> There were some problems with your input.
                    <ul class="mt-2 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Top Navigation -->
            <header class="h-16 border-b bg-white shadow-sm">
                <div class="flex h-full items-center justify-between px-6">

                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = true" class="lg:hidden">
                        <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Page Title -->
                    <h2 class="hidden text-xl font-semibold text-gray-800 lg:block">
                        @yield('page-title', 'Dashboard')
                    </h2>

                    <!-- User Menu -->
                    <div class="flex items-center gap-4">
                        <div class="hidden text-right md:block">
                            <p class="text-sm font-semibold text-gray-700">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role) }}</p>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="rounded-lg bg-red-500 px-4 py-2 text-sm text-white transition hover:bg-red-600">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">

                <!-- Flash Messages -->
                @if (session('success'))
                    <div
                        class="mb-6 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">
                        <span>{{ session('success') }}</span>
                        <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div
                        class="mb-6 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
                        <span>{{ session('error') }}</span>
                        <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif

                @yield('content')

            </main>
        </div>
    </div>
    @stack('scripts')
</body>

</html>
