<div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
        @if ($activities->isEmpty())
            <p>No recent activity.</p>
        @else
            <ul class="space-y-4">
                @foreach ($activities as $activity)
                    <li class="flex items-center justify-between">
                        <div>
                            <p class="text-sm">{{ $activity->description }}</p>
                            <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>