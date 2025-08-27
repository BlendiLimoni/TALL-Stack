<div x-data="{ open: @entangle('isOpen') }" @keydown.window.prevent.cmd.k="open = true" @keydown.window.prevent.ctrl.k="open = true">
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click.away="open = false">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-1/2">
            <div class="p-4">
                <input type="text" wire:model.debounce.300ms="query" class="w-full px-4 py-2 text-sm text-gray-900 bg-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Search for projects...">
            </div>
            @if (strlen($query) > 0)
                <div class="border-t border-gray-200 dark:border-gray-700">
                    @if (count($results) > 0)
                        <ul class="py-1">
                            @foreach ($results as $result)
                                <li>
                                    <a href="{{ route('projects.show', $result) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ $result->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="px-4 py-2 text-sm text-gray-700">No projects found.</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>