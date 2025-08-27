<div class="relative">
    <input type="text" wire:model.debounce.300ms="query" class="w-full px-4 py-2 text-sm text-gray-900 bg-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Search...">

    @if (strlen($query) > 2)
        <div class="absolute z-10 w-full mt-2 bg-white rounded-md shadow-lg">
            @if (count($results) > 0)
                <ul class="py-1">
                    @foreach ($results as $result)
                        <li>
                            @if ($result instanceof \App\Models\Project)
                                <a href="{{ route('projects.show', $result) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Project: {{ $result->name }}</a>
                            @elseif ($result instanceof \App\Models\Task)
                                <a href="{{ route('projects.show', $result->project) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Task: {{ $result->title }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="px-4 py-2 text-sm text-gray-700">No results found.</div>
            @endif
        </div>
    @endif
</div>