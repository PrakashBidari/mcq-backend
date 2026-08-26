<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            <nav class="h-[calc(100vh-4rem)] space-y-1 overflow-y-auto px-4 py-6">

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

                @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'Package'))
                    <!-- Question Set Packages -->
                    <a href="{{ route('packages.index') }}"
                        class="{{ request()->routeIs('packages.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex items-center rounded-lg px-4 py-3 transition">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                            </path>
                        </svg>
                        Question Set Packages
                    </a>
                @endif

                <!-- Divider -->
                <div class="my-4 border-t border-purple-600"></div>

                <!-- Books -->
                @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'Book') || auth()->user()->hasPermission('read', 'BookCategory'))
                    <div x-data="{ open: {{ request()->routeIs('books.*') || request()->routeIs('book-categories.*') ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="open = !open"
                            class="{{ request()->routeIs('books.*') || request()->routeIs('book-categories.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex w-full items-center justify-between rounded-lg px-4 py-3 transition">
                            <div class="flex items-center">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                    </path>
                                </svg>
                                Books
                            </div>
                            <svg :class="open ? 'rotate-180' : ''" class="h-4 w-4 transition-transform duration-200"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="ml-8 space-y-1">
                            @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'Book'))
                                <a href="{{ route('books.index') }}"
                                    class="{{ request()->routeIs('books.*') ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                                    Books
                                </a>
                            @endif
                            @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'BookCategory'))
                                <a href="{{ route('book-categories.index') }}"
                                    class="{{ request()->routeIs('book-categories.*') ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                                    Book Categories
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Blogs -->
                @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'Blog') || auth()->user()->hasPermission('read', 'BlogCategory'))
                    <div x-data="{ open: {{ request()->routeIs('blogs.*') || request()->routeIs('blog-categories.*') ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="open = !open"
                            class="{{ request()->routeIs('blogs.*') || request()->routeIs('blog-categories.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex w-full items-center justify-between rounded-lg px-4 py-3 transition">
                            <div class="flex items-center">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z">
                                    </path>
                                </svg>
                                Blogs
                            </div>
                            <svg :class="open ? 'rotate-180' : ''" class="h-4 w-4 transition-transform duration-200"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="ml-8 space-y-1">
                            @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'Blog'))
                                <a href="{{ route('blogs.index') }}"
                                    class="{{ request()->routeIs('blogs.*') ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                                    Blogs
                                </a>
                            @endif
                            @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'BlogCategory'))
                                <a href="{{ route('blog-categories.index') }}"
                                    class="{{ request()->routeIs('blog-categories.*') ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                                    Blog Categories
                                </a>
                            @endif
                        </div>
                    </div>
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

                <!-- Advertise -->
                @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'Advertise'))
                    <a href="{{ route('advertisements.index') }}"
                        class="{{ request()->routeIs('advertisements.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex items-center gap-3 rounded-lg px-4 py-3 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z">
                            </path>
                        </svg>
                        Advertise
                    </a>
                @endif

                <!-- Payments -->
                @php
                    $paymentModels = ['Purchase', 'PriceTier'];
                    $canSeePayments = auth()->user()->isAdmin() || collect($paymentModels)->contains(fn($m) => auth()->user()->hasPermission('read', $m));
                    $paymentRoutes = ['purchases.*', 'subscribers.*', 'revenue.*', 'price-tiers.*'];
                    $onPaymentsPage = collect($paymentRoutes)->contains(fn($r) => request()->routeIs($r));
                @endphp
                @if ($canSeePayments)
                    <div x-data="{ open: {{ $onPaymentsPage ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="open = !open"
                            class="{{ $onPaymentsPage ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex w-full items-center justify-between rounded-lg px-4 py-3 transition">
                            <div class="flex items-center">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                                Payments
                            </div>
                            <svg :class="open ? 'rotate-180' : ''" class="h-4 w-4 transition-transform duration-200"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="ml-8 space-y-1">
                            @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'Purchase'))
                                <a href="{{ route('purchases.index') }}"
                                    class="{{ request()->routeIs('purchases.*') ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                                    Purchases
                                </a>
                                <a href="{{ route('subscribers.index') }}"
                                    class="{{ request()->routeIs('subscribers.*') ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                                    Subscribers
                                </a>
                                <a href="{{ route('revenue.index') }}"
                                    class="{{ request()->routeIs('revenue.*') ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                                    Revenue
                                </a>
                            @endif
                            @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'PriceTier'))
                                <a href="{{ route('price-tiers.index') }}"
                                    class="{{ request()->routeIs('price-tiers.*') ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                                    Price Tiers
                                </a>
                            @endif
                        </div>
                    </div>
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

                <!-- Banner -->
                @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'Banner'))
                    <a href="{{ route('banners.index') }}"
                        class="{{ request()->routeIs('banners.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex items-center gap-3 rounded-lg px-4 py-3 transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        Banner
                    </a>
                @endif

                <!-- Settings -->
                @if (auth()->user()->isAdmin() || auth()->user()->hasPermission('read', 'AppContent'))
                    <div x-data="{ open: {{ request()->routeIs('app-pages.*') ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="open = !open"
                            class="{{ request()->routeIs('app-pages.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex w-full items-center justify-between rounded-lg px-4 py-3 transition">
                            <div class="flex items-center">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Settings
                            </div>
                            <svg :class="open ? 'rotate-180' : ''" class="h-4 w-4 transition-transform duration-200"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="ml-8 space-y-1">
                            <a href="{{ route('app-pages.edit', 'about-app') }}"
                                class="{{ request()->routeIs('app-pages.edit') && request()->route('slug') === 'about-app' ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                                About App
                            </a>
                            <a href="{{ route('app-pages.edit', 'privacy-policy') }}"
                                class="{{ request()->routeIs('app-pages.edit') && request()->route('slug') === 'privacy-policy' ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                                Privacy Policy
                            </a>
                            <a href="{{ route('app-pages.edit', 'study-library') }}"
                                class="{{ request()->routeIs('app-pages.edit') && request()->route('slug') === 'study-library' ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                                Study Library
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Divider -->
                <div class="my-4 border-t border-purple-600"></div>

                @if (auth()->user()->isAdmin())
                    <!-- Users (Admin Only) -->
                    <div x-data="{ open: {{ request()->routeIs('users.*') ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="open = !open"
                            class="{{ request()->routeIs('users.*') ? 'bg-purple-600' : 'hover:bg-purple-600' }} flex w-full items-center justify-between rounded-lg px-4 py-3 transition">
                            <div class="flex items-center">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                    </path>
                                </svg>
                                Users
                            </div>
                            <svg :class="open ? 'rotate-180' : ''" class="h-4 w-4 transition-transform duration-200"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="ml-8 space-y-1">
                            <a href="{{ route('users.index', ['role' => 'admin']) }}"
                                class="{{ request()->routeIs('users.index') && request('role', 'admin') === 'admin' ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                                Admin
                            </a>
                            <a href="{{ route('users.index', ['role' => 'teacher']) }}"
                                class="{{ request()->routeIs('users.index') && request('role') === 'teacher' ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                                Teacher
                            </a>
                            <a href="{{ route('users.index', ['role' => 'user']) }}"
                                class="{{ request()->routeIs('users.index') && request('role') === 'user' ? 'bg-purple-600' : 'hover:bg-purple-600/50' }} flex items-center rounded-lg px-4 py-2 text-sm transition">
                                User
                            </a>
                        </div>
                    </div>

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
                        <div class="relative">
                            <a href="{{ route('chat.index') }}" id="nav-chat-btn"
                                class="flex items-center gap-2 rounded-lg bg-purple-100 px-3 py-2 text-sm text-purple-600 transition hover:bg-purple-200 {{ request()->routeIs('chat.*') ? 'ring-2 ring-purple-400' : '' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z">
                                    </path>
                                </svg>
                                <span class="hidden font-medium md:inline">Chat</span>
                            </a>
                            <span id="chat-nav-badge"
                                class="absolute -top-1.5 -right-1.5 hidden min-w-[18px] items-center justify-center rounded-full bg-red-500 px-1 py-0.5 text-[10px] font-bold leading-none text-white shadow-md ring-2 ring-white">
                                0
                            </span>
                        </div>

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

    @auth
    @if(in_array(auth()->user()->role, ['admin', 'teacher']))
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
    (function () {
        const badge   = document.getElementById('chat-nav-badge');
        const onChat  = {{ request()->routeIs('chat.*') ? 'true' : 'false' }};
        const CSRF    = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const ME_ID   = {{ auth()->id() }};
        let unread    = 0;

        function updateBadge(n) {
            unread = Math.max(0, n);
            if (unread > 0) {
                badge.textContent = unread > 99 ? '99+' : unread;
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }

        // fetch initial unread count
        fetch('/dashboard/chat/unread', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(counts => {
            const total = Object.values(counts).reduce((s, v) => s + parseInt(v), 0);
            updateBadge(total);
        })
        .catch(() => {});

        // expose so the chat page can control the badge
        window.setChatNavBadge    = (n) => updateBadge(n);
        window.clearChatNavBadge  = ()  => updateBadge(0);
        window.incrementChatNavBadge = () => updateBadge(unread + 1);

        // real-time: subscribe to my private channel
        // skip if we're on the chat page — that page already has its own Pusher instance
        if (!onChat) {
            const pusher = new Pusher("{{ env('REVERB_APP_KEY') }}", {
                wsHost:            "{{ env('REVERB_HOST', 'localhost') }}",
                wsPort:            {{ env('REVERB_PORT', 6001) }},
                forceTLS:          false,
                enabledTransports: ['ws', 'wss'],
                cluster:           '',
                authEndpoint:      '/broadcasting/auth',
                auth: { headers: { 'X-CSRF-TOKEN': CSRF } },
            });

            const channel = pusher.subscribe('private-chat.' + ME_ID);
            channel.bind('message.sent', function (data) {
                // only count messages sent TO me (not my own)
                if (data.sender_id !== ME_ID) {
                    updateBadge(unread + 1);
                }
            });
        }
    })();
    </script>
    @endif
    @endauth
</body>

</html>
