<div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <h3 class="text-lg font-semibold mb-4">Project Statistics</h3>
        @if (empty($stats))
            <p>No projects found.</p>
        @else
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-2xl font-semibold">{{ $stats['project_count'] }}</p>
                    <p class="text-sm text-gray-500">Projects</p>
                </div>
                <div>
                    <p class="text-2xl font-semibold">{{ $stats['task_count'] }}</p>
                    <p class="text-sm text-gray-500">Tasks</p>
                </div>
                <div>
                    <p class="text-2xl font-semibold">{{ $stats['completed_task_count'] }}</p>
                    <p class="text-sm text-gray-500">Completed</p>
                </div>
            </div>
        @endif
    </div>
</div>