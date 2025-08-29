<div class="p-6 space-y-6" x-data="{ open:false }" x-on:close-project-modal.window="open=false" x-init="
    // Listen to both Livewire and browser events
    window.addEventListener('open-project-modal', () => { open=true });
    window.addEventListener('close-project-modal', () => { open=false });
    document.addEventListener('livewire:init', () => {
        Livewire.on('open-project-modal', () => { open = true });
        Livewire.on('close-project-modal', () => { open = false });
        // Temporarily disable JS navigation - let Livewire handle it
        Livewire.on('project-saved', (payload) => {
            console.debug('[project-saved:livewire] letting Livewire handle navigation', payload);
        });
    });
    // Temporarily disable browser event navigation - let Livewire handle it
    window.addEventListener('project-saved', (e) => {
        console.debug('[project-saved:event] letting Livewire handle navigation', e.detail);
    });
">
    <style>[x-cloak]{display:none!important}</style>
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Projects</h1>
    <button class="btn btn-primary" x-on:click="open=true; Livewire.dispatch('create-project')">
            + New Project
        </button>
    </div>

    <div class="flex items-center gap-3">
        <x-input type="text" placeholder="Search projects..." wire:model.live="search" class="w-72" />
        <div class="ml-auto">
            <button class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700" @click="document.documentElement.classList.toggle('dark')">Toggle Dark</button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($projects as $project)
            <a href="{{ route('projects.show', $project) }}" class="p-4 rounded-lg border bg-white dark:bg-gray-800 dark:border-gray-700 hover:shadow relative">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-3 h-3 rounded-full" style="background-color: {{ $project->color ?? '#6366f1' }}"></span>
                        <h2 class="font-medium">{{ $project->name }}</h2>
                    </div>
            <button type="button" class="text-sm text-indigo-600"
                x-on:click.stop.prevent="open=true; Livewire.dispatch('edit-project', { id: {{ $project->id }} })">
                        Edit
                    </button>
                </div>
                <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $project->description }}</p>
                <div class="mt-4 grid grid-cols-3 gap-2 text-xs">
                    <div class="p-2 rounded bg-gray-50 dark:bg-gray-700/50 text-center">
                        <div class="font-semibold">{{ $project->tasks_todo_count ?? 0 }}</div>
                        <div class="text-gray-500">To Do</div>
                    </div>
                    <div class="p-2 rounded bg-gray-50 dark:bg-gray-700/50 text-center">
                        <div class="font-semibold">{{ $project->tasks_in_progress_count ?? 0 }}</div>
                        <div class="text-gray-500">In Progress</div>
                    </div>
                    <div class="p-2 rounded bg-gray-50 dark:bg-gray-700/50 text-center">
                        <div class="font-semibold">{{ $project->tasks_done_count ?? 0 }}</div>
                        <div class="text-gray-500">Done</div>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center text-gray-500">No projects yet.</div>
        @endforelse
    </div>

    <div x-cloak x-show="open" class="fixed inset-0 z-50 grid place-items-center bg-black/40" x-transition
         @close-project-modal.window="open = false">
        <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-lg p-6" @click.outside="open=false">
            <livewire:projects.project-form :key="'project-form-'.now()->timestamp" />
        </div>
    </div>

</div>
