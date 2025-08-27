<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Smart Workspace Manager') }}</title>
    <meta name="description" content="Smart Workspace Manager – TALL stack project manager with teams, roles, Kanban, search, and activity logs.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-white to-gray-50 dark:from-gray-900 dark:to-gray-950 text-gray-900 dark:text-gray-100">
    <header class="border-b border-gray-200/60 dark:border-gray-800/60">
        <div class="mx-auto max-w-7xl px-6 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 font-semibold">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-indigo-600 text-white">SW</span>
                <span>{{ config('app.name', 'Smart Workspace Manager') }}</span>
            </a>
            <nav class="hidden md:flex items-center gap-6 text-sm text-gray-600 dark:text-gray-300">
                <a href="#features" class="hover:text-gray-900 dark:hover:text-white">Features</a>
                <a href="#preview" class="hover:text-gray-900 dark:hover:text-white">Preview</a>
                <a href="https://laravel.com" target="_blank" class="hover:text-gray-900 dark:hover:text-white">Built with TALL</a>
            </nav>
            <div class="flex items-center gap-2">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 rounded-md text-sm font-medium bg-gray-900 text-white hover:bg-black dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white">Register</a>
                        @endif
                    @endauth
                @endif
                <a href="{{ route('demo.login') }}" class="px-4 py-2 rounded-md text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700">Live demo</a>
            </div>
        </div>
    </header>

    <main>
        <section class="relative overflow-hidden">
            <div class="mx-auto max-w-7xl px-6 py-16 lg:py-24">
                <div class="grid lg:grid-cols-12 gap-10 items-center">
                    <div class="lg:col-span-6">
                        <p class="inline-flex items-center gap-2 rounded-full border border-indigo-200/70 dark:border-indigo-900/60 bg-indigo-50/60 dark:bg-indigo-900/20 px-3 py-1 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                            New • TALL stack productivity
                        </p>
                        <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">
                            Run projects with a beautiful Kanban for your team
                        </h1>
                        <p class="mt-4 text-base text-gray-600 dark:text-gray-300 leading-relaxed">
                            Smart Workspace Manager ships with team workspaces, roles, real-time drag-and-drop Kanban, search & filters, activity logs, and notifications — all built on Laravel + Livewire + Alpine + Tailwind.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('demo.login') }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Try live demo</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-700 px-5 py-3 text-sm font-semibold text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                                    Sign up for free
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>