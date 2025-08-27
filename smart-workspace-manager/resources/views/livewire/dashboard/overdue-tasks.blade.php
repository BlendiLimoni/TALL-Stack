<div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <h3 class="text-lg font-semibold mb-4">Overdue Tasks</h3>
        @if ($overdueTasks->isEmpty())
            <p>No overdue tasks. Great job!</p>
        @else
            <ul class="space-y-4">
                @foreach ($overdueTasks as $task)
                    <li class="flex items-center justify-between">
                        <div>
                            <a href="{{ route('projects.show', $task->project) }}" class="font-semibold hover:text-indigo-600">{{ $task->title }}</a>
                            <p class="text-sm text-gray-500">Due: {{ $task->due_date->format('M d, Y') }}</p>
                        </div>
                        <div class="text-sm text-red-500">
                            {{ $task->due_date->diffForHumans() }}
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>