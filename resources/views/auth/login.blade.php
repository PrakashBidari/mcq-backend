<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ikigai Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Same brand palette as layouts/dashboard.blade.php
        tailwind.config = {
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
</head>
<body class="min-h-screen bg-ink-950 text-slate-800 antialiased">

    <div class="flex min-h-screen">

        <!-- Brand panel -->
        <div class="relative hidden w-1/2 overflow-hidden bg-gradient-to-br from-ink-950 via-ink-900 to-purple-900 lg:flex lg:flex-col lg:justify-between lg:p-14">
            <div class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full bg-gold-400/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-40 -left-24 h-[28rem] w-[28rem] rounded-full bg-purple-500/20 blur-3xl"></div>

            <div class="relative flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-gold-300 to-gold-600 shadow-lg shadow-black/40">
                    <svg class="h-6 w-6 text-ink-950" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </span>
                <span class="text-xl font-bold tracking-tight text-white">Ikigai Connect</span>
            </div>

            <div class="relative">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-gold-300">Control panel</p>
                <h1 class="mt-4 text-4xl font-bold leading-tight tracking-tight text-white xl:text-5xl">
                    Everything your learners need, managed in one place.
                </h1>
                <p class="mt-5 max-w-md text-base leading-relaxed text-slate-300">
                    Quizzes, books, payments and support for Ikigai Connect.
                </p>
            </div>

            <p class="relative text-sm text-slate-400">&copy; {{ date('Y') }} Ikigai Connect. All rights reserved.</p>
        </div>

        <!-- Form panel -->
        <div class="flex w-full items-center justify-center bg-[#f4f5fa] p-6 sm:p-10 lg:w-1/2">
            <div class="w-full max-w-md">

                <!-- Brand (small screens) -->
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-gold-300 to-gold-600 shadow">
                        <svg class="h-5 w-5 text-ink-950" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </span>
                    <span class="text-xl font-bold tracking-tight text-ink-950">Ikigai Connect</span>
                </div>

                <!-- Login Card -->
                <div class="rounded-2xl border border-slate-200/70 bg-white p-8 shadow-xl shadow-ink-950/5 sm:p-10">
                    <h2 class="text-2xl font-bold tracking-tight text-ink-950">Welcome back</h2>
                    <p class="mt-1 text-sm text-slate-500">Sign in to manage Ikigai Connect</p>
                    <div class="mt-5 h-1 w-12 rounded-full bg-gradient-to-r from-gold-400 to-gold-600"></div>

                    <!-- Session Status -->
                    @if (session('status'))
                        <div class="mt-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Error Message -->
                    @if (session('error'))
                        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}" class="mt-7">
                        @csrf

                        <!-- Email -->
                        <div class="mb-5">
                            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email Address</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 transition placeholder:text-slate-400 focus:border-purple-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-purple-500/15"
                                placeholder="admin@mcqapp.com"
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-5">
                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 transition placeholder:text-slate-400 focus:border-purple-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-purple-500/15"
                                placeholder="••••••••"
                            >
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-7 flex items-center">
                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                class="h-4 w-4 rounded border-slate-300 accent-purple-600"
                            >
                            <label for="remember" class="ml-2 text-sm text-slate-600">Remember me</label>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            class="w-full rounded-xl bg-gradient-to-r from-ink-900 to-purple-700 px-4 py-3.5 font-semibold text-white shadow-lg shadow-purple-900/25 transition duration-200 hover:from-ink-950 hover:to-purple-800 focus:outline-none focus:ring-4 focus:ring-purple-500/30"
                        >
                            Sign In
                        </button>
                    </form>

                    <!-- Demo Credentials -->
                    <div class="mt-7 rounded-xl border border-gold-200 bg-gold-50 p-4">
                        <p class="mb-2 text-xs font-semibold text-gold-700">Demo Credentials:</p>
                        <p class="text-xs text-slate-600">Admin: admin@mcqapp.com / password</p>
                        <p class="text-xs text-slate-600">Teacher: teacher@mcqapp.com / password</p>
                    </div>
                </div>

                <!-- Footer (small screens) -->
                <p class="mt-6 text-center text-sm text-slate-500 lg:hidden">
                    &copy; {{ date('Y') }} Ikigai Connect. All rights reserved.
                </p>
            </div>
        </div>
    </div>

</body>
</html>
