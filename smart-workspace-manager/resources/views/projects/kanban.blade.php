<div class="p-6" wire:key="kanban-{{ $project->id }}">
    <h1 class="text-2xl font-semibold">{{ $project->name }}</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <div>
            <h2 class="font-medium">To Do</h2>
        </div>
        <div>
            <h2 class="font-medium">In Progress</h2>
        </div>
        <div>
            <h2 class="font-medium">Done</h2>
        </div>
    </div>
</div>
