<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Ikigai Connect</title>

    <!-- Theme: applied before first paint so a dark-mode page never flashes light.
         Saved choice wins; otherwise follow the device setting. -->
    <script>
        (function() {
            var saved = null;
            try { saved = localStorage.getItem('theme'); } catch (e) {}
            var dark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', dark);
        })();

        window.toggleTheme = function() {
            var dark = document.documentElement.classList.toggle('dark');
            try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (e) {}
            window.dispatchEvent(new CustomEvent('themechange', { detail: { dark: dark } }));
        };
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Brand palette. Every admin page is written with Tailwind's "purple"
        // utilities, so the brand colour is swapped in by redefining that scale
        // (royal indigo) rather than editing each page. "gold" is the accent.
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        purple: {
                            50: '#eef1fb', 100: '#dfe4f8', 200: '#c3cbf1', 300: '#9ba8e6', 400: '#7181d8',
                            500: '#4f5fc9', 600: '#3b47b0', 700: '#30398e', 800: '#293171', 900: '#232a5a', 950: '#151936',
                        },
                        gold: {
                            50: '#fbf7ed', 100: '#f5ebd1', 200: '#eddba9', 300: '#e3c781', 400: '#d4b160',
                            500: '#c19a4b', 600: '#a37f37', 700: '#82632c',
                        },
                        ink: { 800: '#161d3d', 900: '#0f1530', 950: '#0a0f24' },
                    },
                },
            },
        };
    </script>

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

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        /* Sidebar scrollbar */
        .app-sidebar nav::-webkit-scrollbar { width: 6px; }
        .app-sidebar nav::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, .25); border-radius: 999px; }

        /* DataTables controls */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: .5rem;
            padding: .4rem .75rem;
            font-size: .875rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: .375rem;
            margin: 0 .125rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #3b47b0 !important;
            border-color: #3b47b0 !important;
            color: #fff !important;
        }

        /* ── Dark theme ──────────────────────────────────────────────────────────
           Every admin page is written with light Tailwind utilities (bg-white,
           text-gray-800, ...). Rather than add a dark: variant to each of them, the
           utilities are remapped here whenever <html> carries the "dark" class, so
           all pages follow the theme switch. */
        html.dark { color-scheme: dark; }
        html.dark body { background-color: #0b1120; color: #e2e8f0; }

        html.dark .app-main .bg-white { background-color: #111827 !important; }
        html.dark .app-main .bg-gray-50 { background-color: #0f172a !important; }
        html.dark .app-main .bg-gray-100 { background-color: #1e293b !important; }
        html.dark .app-main .bg-gray-200 { background-color: #334155 !important; }

        html.dark .app-main .text-gray-900,
        html.dark .app-main .text-gray-800 { color: #f1f5f9 !important; }
        html.dark .app-main .text-gray-700 { color: #cbd5e1 !important; }
        html.dark .app-main .text-gray-600,
        html.dark .app-main .text-gray-500 { color: #94a3b8 !important; }
        html.dark .app-main .text-gray-400 { color: #64748b !important; }

        html.dark .app-main :is(.border, .border-2, .border-b, .border-t, .border-l, .border-r):not([class*="border-purple"], [class*="border-red"], [class*="border-green"], [class*="border-blue"], [class*="border-yellow"], [class*="border-orange"], [class*="border-transparent"]),
        html.dark .app-main .border-gray-100,
        html.dark .app-main .border-gray-200,
        html.dark .app-main .border-gray-300 { border-color: #1f2937 !important; }
        html.dark .app-main .divide-gray-100 > * + *,
        html.dark .app-main .divide-gray-200 > * + * { border-color: #1f2937 !important; }

        html.dark .app-main .shadow-sm,
        html.dark .app-main .shadow,
        html.dark .app-main .shadow-lg { box-shadow: 0 0 0 1px #1f2937 !important; }

        html.dark .app-main .hover\:bg-gray-50:hover,
        html.dark .app-main .hover\:bg-gray-100:hover { background-color: #1e293b !important; }
        html.dark .app-main .hover\:bg-gray-200:hover,
        html.dark .app-main .hover\:bg-gray-300:hover { background-color: #475569 !important; }

        html.dark .app-main .hover\:bg-purple-50:hover, html.dark .app-main .hover\:bg-purple-200:hover { background-color: rgba(113, 129, 216, .28) !important; }
        html.dark .app-main .hover\:bg-blue-50:hover { background-color: rgba(59, 130, 246, .28) !important; }
        html.dark .app-main .hover\:bg-red-50:hover { background-color: rgba(239, 68, 68, .28) !important; }
        html.dark .app-main .hover\:bg-green-50:hover { background-color: rgba(34, 197, 94, .28) !important; }

        /* Form controls */
        html.dark .app-main input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="submit"]):not([type="button"]),
        html.dark .app-main select,
        html.dark .app-main textarea {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
            border-color: #334155 !important;
        }
        html.dark .app-main input::placeholder,
        html.dark .app-main textarea::placeholder { color: #64748b; }
        html.dark .app-main code { background-color: #1e293b !important; color: #e2e8f0; }

        /* Tinted badges / alerts: swap the pastel fill for a translucent one and
           lift the text so it stays readable on a dark surface */
        html.dark .app-main .bg-purple-50, html.dark .app-main .bg-purple-100 { background-color: rgba(113, 129, 216, .18) !important; }
        html.dark .app-main .bg-blue-50, html.dark .app-main .bg-blue-100 { background-color: rgba(59, 130, 246, .16) !important; }
        html.dark .app-main .bg-green-50, html.dark .app-main .bg-green-100 { background-color: rgba(34, 197, 94, .16) !important; }
        html.dark .app-main .bg-red-50, html.dark .app-main .bg-red-100 { background-color: rgba(239, 68, 68, .16) !important; }
        html.dark .app-main .bg-yellow-50, html.dark .app-main .bg-yellow-100 { background-color: rgba(234, 179, 8, .16) !important; }
        html.dark .app-main .bg-orange-50, html.dark .app-main .bg-orange-100 { background-color: rgba(249, 115, 22, .16) !important; }

        html.dark .app-main .text-purple-600, html.dark .app-main .text-purple-700, html.dark .app-main .text-purple-800 { color: #b4bef0 !important; }
        html.dark .app-main .text-blue-600, html.dark .app-main .text-blue-700, html.dark .app-main .text-blue-800 { color: #93c5fd !important; }
        html.dark .app-main .text-green-600, html.dark .app-main .text-green-700, html.dark .app-main .text-green-800 { color: #86efac !important; }
        html.dark .app-main .text-red-500, html.dark .app-main .text-red-600, html.dark .app-main .text-red-700 { color: #fca5a5 !important; }
        html.dark .app-main .text-yellow-600, html.dark .app-main .text-yellow-700, html.dark .app-main .text-yellow-800 { color: #fde047 !important; }
        html.dark .app-main .text-orange-600, html.dark .app-main .text-orange-700 { color: #fdba74 !important; }

        html.dark .app-main .border-purple-200, html.dark .app-main .border-blue-200,
        html.dark .app-main .border-green-200, html.dark .app-main .border-red-200,
        html.dark .app-main .border-yellow-200, html.dark .app-main .border-orange-200 { border-color: rgba(148, 163, 184, .25) !important; }

        /* DataTables */
        html.dark .dataTables_wrapper,
        html.dark .dataTables_wrapper .dataTables_length,
        html.dark .dataTables_wrapper .dataTables_filter,
        html.dark .dataTables_wrapper .dataTables_info,
        html.dark .dataTables_wrapper .dataTables_paginate .paginate_button { color: #94a3b8 !important; }
        html.dark table.dataTable thead th,
        html.dark table.dataTable thead td { border-bottom-color: #334155 !important; color: #cbd5e1; }
        html.dark table.dataTable.no-footer { border-bottom-color: #334155 !important; }
        html.dark table.dataTable tbody tr { background-color: transparent !important; }
        html.dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }
    </style>

    @stack('styles')
</head>

@php
    $navUser = auth()->user();
    $navCan = fn (string $model) => $navUser->isAdmin() || $navUser->hasPermission('read', $model);
    $navIs = fn (string ...$patterns) => request()->routeIs(...$patterns);
    $navPage = fn (string $slug) => $navIs('app-pages.*') && request()->route('slug') === $slug;
    $navRole = fn (string $role) => $navIs('users.*') && request('role', 'admin') === $role;

    // Sidebar, grouped by area. Item = [label, icon, url, is current page, is visible]
    $navSections = [
        ['key' => 'quiz', 'label' => 'Quiz', 'items' => [
            ['Categories', 'tag', route('categories.index'), $navIs('categories.*'), $navCan('Category')],
            ['Question Sets', 'folder', route('question-sets.index'), $navIs('question-sets.*'), $navCan('QuestionSet')],
            ['Questions', 'question', route('questions.index'), $navIs('questions.*'), $navCan('Question')],
            ['Packages', 'cube', route('packages.index'), $navIs('packages.*'), $navCan('Package')],
        ]],
        ['key' => 'books', 'label' => 'Books', 'items' => [
            ['Books', 'book', route('books.index'), $navIs('books.*'), $navCan('Book')],
            ['Book Categories', 'collection', route('book-categories.index'), $navIs('book-categories.*'), $navCan('BookCategory')],
        ]],
        ['key' => 'blog', 'label' => 'Blog', 'items' => [
            ['Blog Posts', 'newspaper', route('blogs.index'), $navIs('blogs.*'), $navCan('Blog')],
            ['Blog Categories', 'collection', route('blog-categories.index'), $navIs('blog-categories.*'), $navCan('BlogCategory')],
        ]],
        ['key' => 'payments', 'label' => 'Payments', 'items' => [
            ['Purchases', 'card', route('purchases.index'), $navIs('purchases.*'), $navCan('Purchase')],
            ['Subscribers', 'star', route('subscribers.index'), $navIs('subscribers.*'), $navCan('Purchase')],
            ['Revenue', 'trending', route('revenue.index'), $navIs('revenue.*'), $navCan('Purchase')],
            ['Price Tiers', 'yen', route('price-tiers.index'), $navIs('price-tiers.*'), $navCan('PriceTier')],
        ]],
        ['key' => 'users', 'label' => 'Users', 'items' => [
            ['App Users', 'user-group', route('users.index', ['role' => 'user']), $navRole('user'), $navUser->isAdmin()],
            ['Teachers', 'users', route('users.index', ['role' => 'teacher']), $navRole('teacher'), $navUser->isAdmin()],
            ['Admins', 'shield', route('users.index', ['role' => 'admin']), $navRole('admin'), $navUser->isAdmin()],
        ]],
        ['key' => 'auth', 'label' => 'Auth & Access', 'items' => [
            ['Permissions', 'lock', route('permissions.index'), $navIs('permissions.*'), $navUser->isAdmin()],
        ]],
        ['key' => 'support', 'label' => 'Support', 'items' => [
            ['Chat', 'chat', route('chat.index'), $navIs('chat.*'), true],
            ['Contact Messages', 'inbox', route('contact-messages.index'), $navIs('contact-messages.*'), true],
            ['Contact Settings', 'mail', route('contact-settings.index'), $navIs('contact-settings.*'), true],
            ['FAQs', 'question', route('faqs.index'), $navIs('faqs.*'), $navCan('Faq')],
        ]],
        ['key' => 'content', 'label' => 'App Content', 'items' => [
            ['Banners', 'photo', route('banners.index'), $navIs('banners.*'), $navCan('Banner')],
            ['Advertisements', 'megaphone', route('advertisements.index'), $navIs('advertisements.*'), $navCan('Advertise')],
            ['About App', 'info', route('app-pages.edit', 'about-app'), $navPage('about-app'), $navCan('AppContent')],
            ['Privacy Policy', 'document', route('app-pages.edit', 'privacy-policy'), $navPage('privacy-policy'), $navCan('AppContent')],
            ['Study Library', 'book', route('app-pages.edit', 'study-library'), $navPage('study-library'), $navCan('AppContent')],
        ]],
    ];

    $navItemBase = 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition';
    $navItemOn = 'bg-gradient-to-r from-gold-400/20 to-gold-400/5 text-gold-200 ring-1 ring-inset ring-gold-400/25';
    $navItemOff = 'text-slate-300 hover:bg-white/5 hover:text-white';
@endphp

<body class="bg-[#f4f5fa] text-slate-800 antialiased" x-data="{ sidebarOpen: window.innerWidth >= 1024 }">

    <div class="flex h-screen overflow-hidden">

        <!-- Mobile backdrop -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="app-sidebar fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-white/5 bg-gradient-to-b from-ink-950 via-ink-900 to-ink-800 text-white transition-transform duration-300 ease-in-out lg:static lg:inset-0 lg:translate-x-0">
            <!-- Logo -->
            <div class="flex h-16 shrink-0 items-center justify-between px-5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-gold-300 to-gold-600 shadow-lg shadow-black/40">
                        @include('partials.icon', ['name' => 'bolt', 'class' => 'h-5 w-5 text-ink-950'])
                    </span>
                    <span>
                        <span class="block text-base font-bold leading-tight tracking-tight">Ikigai Connect</span>
                        <span class="block text-[11px] uppercase leading-tight tracking-wider text-gold-300/80">Control panel</span>
                    </span>
                </a>
                <button @click="sidebarOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-white/5 hover:text-white lg:hidden" aria-label="Close menu">
                    @include('partials.icon', ['name' => 'close', 'class' => 'h-5 w-5'])
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 space-y-5 overflow-y-auto px-3 pb-6 pt-2">

                <a href="{{ route('dashboard') }}"
                    class="{{ $navItemBase }} {{ $navIs('dashboard') ? $navItemOn : $navItemOff }}">
                    @include('partials.icon', ['name' => 'home', 'class' => 'h-5 w-5 shrink-0'])
                    Dashboard
                </a>

                @foreach ($navSections as $section)
                    @php
                        $visibleItems = array_filter($section['items'], fn ($item) => $item[4]);
                        $sectionActive = collect($visibleItems)->contains(fn ($item) => $item[3]);
                    @endphp
                    @if ($visibleItems)
                        {{-- Collapsed/expanded state is remembered per section; the section
                             holding the current page always starts open --}}
                        <div x-data="{
                                open: {{ $sectionActive ? 'true' : 'false' }} || localStorage.getItem('nav:{{ $section['key'] }}') !== '0',
                                toggle() {
                                    this.open = !this.open;
                                    localStorage.setItem('nav:{{ $section['key'] }}', this.open ? '1' : '0');
                                }
                            }">
                            <button type="button" @click="toggle()"
                                class="flex w-full items-center justify-between px-3 pb-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500 transition hover:text-slate-300">
                                {{ $section['label'] }}
                                <span :class="open ? '' : '-rotate-90'" class="transition-transform duration-200">
                                    @include('partials.icon', ['name' => 'chevron-down', 'class' => 'h-3.5 w-3.5', 'stroke' => 2.5])
                                </span>
                            </button>
                            <div x-show="open" x-transition.opacity class="space-y-1">
                                @foreach ($visibleItems as [$label, $icon, $url, $active])
                                    <a href="{{ $url }}" class="{{ $navItemBase }} {{ $active ? $navItemOn : $navItemOff }}">
                                        @include('partials.icon', ['name' => $icon, 'class' => 'h-5 w-5 shrink-0'])
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="app-main flex flex-1 flex-col overflow-hidden">

            <!-- Top Navigation -->
            <header class="h-16 shrink-0 border-b border-gray-200 bg-white">
                <div class="flex h-full items-center justify-between gap-4 px-4 sm:px-6">

                    <div class="flex min-w-0 items-center gap-3">
                        <!-- Mobile menu button -->
                        <button @click="sidebarOpen = true" class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 lg:hidden" aria-label="Open menu">
                            @include('partials.icon', ['name' => 'menu', 'class' => 'h-6 w-6'])
                        </button>

                        <!-- Page Title -->
                        <h2 class="truncate text-lg font-semibold text-gray-800">
                            @yield('page-title', 'Dashboard')
                        </h2>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        <!-- Theme switch -->
                        <button type="button" onclick="toggleTheme()"
                            class="rounded-lg border border-gray-200 p-2 text-gray-600 transition hover:bg-gray-100"
                            title="Switch between light and dark theme" aria-label="Switch between light and dark theme">
                            <span class="dark:hidden">@include('partials.icon', ['name' => 'moon', 'class' => 'h-5 w-5'])</span>
                            <span class="hidden dark:inline">@include('partials.icon', ['name' => 'sun', 'class' => 'h-5 w-5'])</span>
                        </button>

                        <div class="relative">
                            <a href="{{ route('chat.index') }}" id="nav-chat-btn"
                                class="flex items-center gap-2 rounded-lg bg-purple-100 px-3 py-2 text-sm text-purple-600 transition hover:bg-purple-200 {{ request()->routeIs('chat.*') ? 'ring-2 ring-purple-400' : '' }}">
                                @include('partials.icon', ['name' => 'chat', 'class' => 'h-5 w-5'])
                                <span class="hidden font-medium md:inline">Chat</span>
                            </a>
                            <span id="chat-nav-badge"
                                class="absolute -top-1.5 -right-1.5 hidden min-w-[18px] items-center justify-center rounded-full bg-red-500 px-1 py-0.5 text-[10px] font-bold leading-none text-white shadow-md ring-2 ring-white">
                                0
                            </span>
                        </div>

                        <div class="hidden items-center gap-3 border-l border-gray-200 pl-3 md:flex">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-purple-600 to-purple-900 text-sm font-bold text-gold-200 ring-2 ring-gold-400/40">
                                {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <div class="text-left">
                                <p class="text-sm font-semibold leading-tight text-gray-700">{{ auth()->user()->name }}</p>
                                <p class="text-xs leading-tight text-gray-500">{{ ucfirst(auth()->user()->role) }}</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-red-50 hover:text-red-600"
                                title="Log out">
                                @include('partials.icon', ['name' => 'logout', 'class' => 'h-5 w-5'])
                                <span class="hidden sm:inline">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6">

                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                        <strong>Whoops!</strong> There were some problems with your input.
                        <ul class="mt-2 list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Flash Messages -->
                @if (session('success'))
                    <div
                        class="mb-6 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">
                        <span>{{ session('success') }}</span>
                        <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900" aria-label="Dismiss">
                            @include('partials.icon', ['name' => 'close', 'class' => 'h-5 w-5'])
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div
                        class="mb-6 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
                        <span>{{ session('error') }}</span>
                        <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900" aria-label="Dismiss">
                            @include('partials.icon', ['name' => 'close', 'class' => 'h-5 w-5'])
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
