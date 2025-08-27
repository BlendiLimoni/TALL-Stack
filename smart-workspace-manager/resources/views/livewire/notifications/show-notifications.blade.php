<div class="relative">
    <button @click="open = ! open" class="relative z-10 block h-8 w-8 rounded-full overflow-hidden border-2 border-gray-600 focus:outline-none focus:border-white">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if (auth()->user()->unreadNotifications->count())
            <span class="absolute top-0 right-0 h-2 w-2 mt-1 mr-1 bg-red-500 rounded-full"></span>
        @endif
    </button>

    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden z-20">
        <div class="p-4 text-gray-900 dark:text-gray-100">
            <h3 class="text-lg font-semibold">Notifications</h3>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($notifications as $notification)
                <div class="p-4 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <a href="{{ $notification->data['url'] ?? '#' }}" class="block">
                        <p class="text-sm">{{ $notification->data['message'] }}</p>
                        <p class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                    </a>
                </div>
            @empty
                <div class="p-4">
                    <p class="text-sm text-gray-500">No new notifications.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>