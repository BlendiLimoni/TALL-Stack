<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles & Vite CSS -->
    @vite(['resources/css/app.css'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @livewire('navigation-menu')

            @auth
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-2 flex justify-end">
                    <div x-data="{ open:false }" class="relative">
                        <button class="text-sm px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800" @click="open=!open">Notifications</button>
                        <div x-show="open" @click.outside="open=false" class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 border dark:border-gray-700 rounded shadow-lg p-2 max-h-80 overflow-auto">
                            @php($notes = auth()->user()->unreadNotifications()->latest()->limit(10)->get())
                            @forelse($notes as $n)
                                <div class="px-2 py-2 text-sm border-b dark:border-gray-700">
                                    <div class="font-medium">{{ data_get($n->data,'message') }}</div>
                                    <div class="text-xs text-gray-500">{{ data_get($n->data,'title') }} · {{ data_get($n->data,'project') }}</div>
                                </div>
                            @empty
                                <div class="px-2 py-4 text-center text-gray-500 text-sm">No notifications</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endauth

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

    @livewireScripts
    <!-- Vite JS after Livewire to avoid Alpine entangle errors -->
    @vite(['resources/js/app.js'])
    @livewire('command-palette')
        <script>
            // Toast handler - declare only once globally
            window.coerceToastPayload = window.coerceToastPayload || function(...args) {
                if (!args || args.length === 0) return {};
                const first = args[0];
                if (first && typeof first === 'object' && !Array.isArray(first)) return first;
                if (Array.isArray(first) && first[0] && typeof first[0] === 'object') return first[0];
                if (args.length >= 2 && typeof args[0] === 'string') return { type: args[0], message: args[1] };
                return {};
            };
            window.showToast = window.showToast || function(payload = {}) {
                const { type, message } = payload || {};
                const el = document.createElement('div');
                el.className = `fixed top-4 right-4 px-4 py-2 rounded shadow text-white ${type==='success'?'bg-emerald-600':'bg-rose-600'}`;
                el.textContent = message || 'Done';
                document.body.appendChild(el);
                setTimeout(() => el.remove(), 3000);
            };
            
            window.addEventListener('toast', (e) => { console.debug('[toast:event]', e.detail); window.showToast(window.coerceToastPayload(e.detail)); });
            document.addEventListener('livewire:init', () => {
                console.debug('[livewire:init] ready');
                if (window.Livewire?.on) {
                    Livewire.on('toast', (...args) => { const payload = window.coerceToastPayload(...args); console.debug('[toast:livewire]', payload); window.showToast(payload); });
                }
            });
            // Log all Livewire lifecycle events for debugging
            document.addEventListener('livewire:navigate', (e) => console.debug('[livewire:navigate]', e.detail));
            document.addEventListener('livewire:navigated', (e) => console.debug('[livewire:navigated]', e.detail));
            document.addEventListener('livewire:before-request', (e) => console.debug('[livewire:before-request]', e.detail));
            document.addEventListener('livewire:response', (e) => console.debug('[livewire:response]', e.detail));
            document.addEventListener('livewire:error', (e) => console.debug('[livewire:error]', e.detail));
            // Session-flashed toast (after redirect)
            @if (session('toast'))
                (function(){
                    try { const t = @json(session('toast')); console.debug('[toast:session]', t); window.showToast(t); } catch(e) {}
                })();
            @endif
        </script>
    </body>
</html>
