<div wire:poll.5s class="relative">
    <!-- Activity Bell Icon -->
    <button 
        @click="open = !open" 
        class="relative text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition duration-150 ease-in-out"
        x-data="{ open: false }"
    >
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        
        @if($showNotificationDot || $hasNewActivities)
            <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400 ring-2 ring-white"></span>
        @endif

        <!-- Dropdown -->
        <div 
            x-show="open" 
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            @click.away="open = false"
            class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl border dark:border-gray-700 z-50"
            style="display: none;"
        >
            <!-- Header -->
            <div class="px-4 py-3 border-b dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">Team Activity</h3>
                    @if($showNotificationDot || $hasNewActivities)
                        <button 
                            wire:click="markAsRead" 
                            class="text-xs text-blue-600 hover:text-blue-700"
                        >
                            Mark as read
                        </button>
                    @endif
                </div>
            </div>

            <!-- Activity List -->
            <div class="max-h-80 overflow-y-auto">
                @forelse($recentActivities as $activity)
                    <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 border-b dark:border-gray-700 last:border-b-0
                                {{ $hasNewActivities && in_array($activity->id, collect($newActivities)->pluck('id')->toArray()) ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                        
                        <div class="flex items-start space-x-3">
                            <!-- User Avatar -->
                            <div class="flex-shrink-0">
                                @if($activity->user)
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                        <span class="text-xs font-medium text-gray-700">
                                            {{ substr($activity->user->name, 0, 2) }}
                                        </span>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Activity Content -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <span class="font-medium">{{ $activity->user->name ?? 'System' }}</span>
                                    <span class="text-gray-600 dark:text-gray-400">
                                        {{ $this->formatActivityAction($activity->action) }}
                                    </span>
                                </p>
                                
                                @if($activity->meta && is_array($activity->meta))
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        @if(isset($activity->meta['task_title']))
                                            Task: {{ $activity->meta['task_title'] }}
                                        @elseif(isset($activity->meta['project_name']))
                                            Project: {{ $activity->meta['project_name'] }}
                                        @endif
                                    </p>
                                @endif

                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $activity->created_at->diffForHumans() }}
                                </p>
                            </div>

                            <!-- New indicator -->
                            @if($hasNewActivities && in_array($activity->id, collect($newActivities)->pluck('id')->toArray()))
                                <div class="flex-shrink-0">
                                    <span class="inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity</p>
                    </div>
                @endforelse
            </div>

            @if($recentActivities->count() > 0)
                <!-- Footer -->
                <div class="px-4 py-3 border-t dark:border-gray-700">
                    <a href="{{ route('reports.index') }}" class="text-xs text-blue-600 hover:text-blue-700">
                        View all activity →
                    </a>
                </div>
            @endif
        </div>
    </button>
</div>

@script
<script>
// Format activity action for better readability
window.formatActivityAction = function(action) {
    const actions = {
        'task.created': 'created a task',
        'task.updated': 'updated a task', 
        'task.deleted': 'deleted a task',
        'task.assigned': 'assigned a task',
        'task.moved': 'moved a task',
        'project.created': 'created a project',
        'project.updated': 'updated a project',
        'project.deleted': 'deleted a project',
        'team.joined': 'joined the team',
        'file.uploaded': 'uploaded a file',
    };
    
    return actions[action] || action.replace(/[._]/g, ' ');
};
</script>
@endscript
