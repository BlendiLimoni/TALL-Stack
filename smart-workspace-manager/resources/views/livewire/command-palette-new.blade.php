<div>
    <!-- Command Palette Trigger (invisible, just for keyboard shortcut) -->
    <div x-data="{ 
            showModal: false,
            init() {
                // Force close on initialization
                this.showModal = false;
                this.$wire.closePalette();
            }
         }"
         @keydown.window.cmd.k.prevent="showModal = true; $wire.openPalette()"
         @keydown.window.ctrl.k.prevent="showModal = true; $wire.openPalette()"
         @keydown.window.escape="showModal = false; $wire.closePalette()"
         @palette-closed.window="showModal = false">
        
        <!-- Modal Backdrop -->
        <div x-show="showModal || @js($isOpen)" 
             x-transition.opacity.duration.300ms
             class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center"
             @click.self="showModal = false; $wire.closePalette()"
             style="display: none;">
            
            <!-- Modal Content -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl mx-4"
                 @click.stop>
                
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Search Projects</h3>
                    <button @click="showModal = false; $wire.closePalette()" 
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Search Input -->
                <div class="p-4">
                    <input type="text" 
                           wire:model.live.debounce.300ms="query" 
                           placeholder="Search for projects..."
                           class="w-full px-4 py-3 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                           autofocus>
                </div>
                
                <!-- Results -->
                @if (strlen($query) > 0)
                    <div class="border-t border-gray-200 dark:border-gray-700 max-h-64 overflow-y-auto">
                        @if (count($results) > 0)
                            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($results as $result)
                                    <li>
                                        <a href="{{ route('projects.show', $result) }}" 
                                           @click="showModal = false; $wire.closePalette()"
                                           class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $result->name }}</div>
                                            @if($result->description)
                                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($result->description, 80) }}</div>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No projects found for "{{ $query }}"
                            </div>
                        @endif
                    </div>
                @else
                    <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        <div class="mb-2">Start typing to search projects...</div>
                        <div class="text-xs">
                            Press <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">Ctrl</kbd> + 
                            <kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs">K</kbd> to open
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
