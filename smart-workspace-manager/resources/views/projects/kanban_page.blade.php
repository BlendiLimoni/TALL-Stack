<div class="p-6 space-y-6" wire:key="kanban-{{ $project->id }}">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('projects.index') }}" class="text-sm text-gray-500 hover:underline">← Back</a>
            <h1 class="text-2xl font-semibold mt-1">{{ $project->name }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <input type="text" placeholder="Search tasks" class="input" wire:model.debounce.300ms="filter" />
            <select class="input" wire:model="priority">
                <option value="">All priorities</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
            <button class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700" @click="document.documentElement.classList.toggle('dark')">Dark</button>
            <x-button wire:click="openTaskModal()">+ Task</x-button>
        </div>
    </div>

    @php
        $columns = [
            'todo' => 'To Do',
            'in_progress' => 'In Progress',
            'done' => 'Done',
        ];
        $tasks = $this->tasks;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @foreach($columns as $key => $label)
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="font-medium">{{ $label }}</h2>
                    <span class="text-xs text-gray-500">{{ ($tasks[$key] ?? collect())->count() }}</span>
                </div>
                <div class="space-y-2" style="min-height:200px"
                     x-data="{ status: '{{ $key }}' }" 
                     @dragover.prevent 
                     @drop="
                        const data = JSON.parse($event.dataTransfer.getData('text/plain'));
                        const items = [...$el.querySelectorAll('[data-id]')];
                        const afterElement = getDragAfterElement($el, $event.clientY);
                        let order = items.length;
                        if (afterElement) {
                            order = items.indexOf(afterElement);
                        }
                        const id = $el.closest('[wire\\:id]')?.getAttribute('wire:id');
                        const lw = window.Livewire?.find(id);
                        lw && lw.call('reorderTask', { taskId: Number(data.id), status: status, order: Math.max(0, order) });
                     ">
                    @foreach(($tasks[$key] ?? collect()) as $task)
                        <div class="task-card p-3 rounded border bg-white dark:bg-gray-900 dark:border-gray-700 cursor-move"
                             draggable="true" 
                             data-id="{{ $task->id }}" 
                             wire:key="task-{{ $task->id }}"
                             @dragstart="
                                $event.dataTransfer.setData('text/plain', JSON.stringify({ id: '{{ $task->id }}', fromStatus: '{{ $key }}' }));
                                $event.dataTransfer.effectAllowed = 'move';
                                $event.target.classList.add('opacity-50', 'dragging');
                             " 
                             @dragend="$event.target.classList.remove('opacity-50', 'dragging')">
                            <div class="flex items-center justify-between">
                                <div class="font-medium">{{ $task->title }}</div>
                                <div class="text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700">{{ ucfirst($task->priority) }}</div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                @if($task->assignee)
                                    Assigned: {{ $task->assignee->name }}
                                @endif
                                @if($task->due_date)
                                    · Due {{ $task->due_date->format('M j') }}
                                @endif
                            </div>
                            <div class="mt-2 flex gap-2">
                                <button type="button" class="text-xs text-indigo-600" draggable="false" @mousedown.prevent.stop wire:click.stop="openTaskModal({{ $task->id }})">Edit</button>
                                <button type="button" class="text-xs text-rose-600" draggable="false" @mousedown.prevent.stop wire:click.stop="deleteTask({{ $task->id }})">Delete</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 md:col-span-1">
            <h2 class="font-medium mb-2">Activity</h2>
            <div class="space-y-2 overflow-auto text-sm" style="max-height:420px">
                @foreach($logs as $log)
                    <div class="text-gray-600 dark:text-gray-300">
                        <span class="font-medium">{{ optional($log->user)->name ?? 'System' }}</span>
                        {{ str_replace(['task.', 'project.'], '', $log->action) }}
                        <span class="text-xs text-gray-400">· {{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if($showTaskModal)
        <div class="fixed inset-0 z-50 grid place-items-center bg-black/40">
            <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-xl p-6">
                <h3 class="text-lg font-semibold mb-4">{{ $taskId ? 'Edit Task' : 'New Task' }}</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="label">Title</label>
                        <input class="input w-full" wire:model.defer="form.title" />
                        @error('form.title')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-span-2">
                        <label class="label">Description</label>
                        <textarea class="input w-full" rows="4" wire:model.defer="form.description"></textarea>
                        @error('form.description')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="label">Due Date</label>
                        <input type="date" class="input w-full" wire:model.defer="form.due_date" />
                        @error('form.due_date')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="label">Priority</label>
                        <select class="input w-full" wire:model.defer="form.priority">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        @error('form.priority')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select class="input w-full" wire:model.defer="form.status">
                            <option value="todo">To Do</option>
                            <option value="in_progress">In Progress</option>
                            <option value="done">Done</option>
                        </select>
                        @error('form.status')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-span-2">
                        <label class="label">Assignee</label>
                        <select class="input w-full" wire:model.defer="form.assigned_user_id">
                            <option value="">Unassigned</option>
                            @foreach($teamUsers as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} &lt;{{ $u->email }}&gt;</option>
                            @endforeach
                        </select>
                        @error('form.assigned_user_id')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <x-secondary-button wire:click="closeTaskModal" wire:loading.attr="disabled">Cancel</x-secondary-button>
                    <x-button wire:click="saveFromForm" wire:loading.attr="disabled">
                        <span wire:loading.remove>Save</span>
                        <span wire:loading>Saving...</span>
                    </x-button>
                </div>
            </div>
        </div>
    @endif

    @verbatim
    <script>
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('[data-id]:not(.dragging)')];
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }
    </script>
    @endverbatim
</div>
