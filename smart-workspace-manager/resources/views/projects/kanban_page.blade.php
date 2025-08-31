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
                            
                            <!-- File Attachments Preview -->
                            @if($task->attachments->count() > 0)
                                <div class="mt-2 p-2 bg-gray-50 dark:bg-gray-800 rounded text-xs">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-medium flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                            {{ $task->attachments->count() }} file{{ $task->attachments->count() > 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                    <div class="space-y-1">
                                        @foreach($task->attachments->take(3) as $attachment)
                                            <div class="flex items-center justify-between">
                                                <span class="truncate flex-1 mr-2">{{ $attachment->filename }}</span>
                                                <div class="flex gap-1">
                                                    <a href="{{ $attachment->url }}" 
                                                       download="{{ $attachment->filename }}"
                                                       class="text-blue-600 hover:text-blue-700 underline"
                                                       draggable="false"
                                                       @mousedown.prevent.stop
                                                       @click.stop>
                                                        Download
                                                    </a>
                                                    @if($attachment->is_image)
                                                        <button type="button"
                                                                @click.stop="window.open('{{ $attachment->url }}', '_blank')"
                                                                class="text-green-600 hover:text-green-700 underline ml-1"
                                                                draggable="false"
                                                                @mousedown.prevent.stop>
                                                            View
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                        @if($task->attachments->count() > 3)
                                            <div class="text-gray-400 text-center">
                                                +{{ $task->attachments->count() - 3 }} more file{{ $task->attachments->count() - 3 > 1 ? 's' : '' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            
                            <div class="mt-2 flex gap-2">
                                <button type="button" class="text-xs text-indigo-600" draggable="false" @mousedown.prevent.stop wire:click.stop="openTaskModal({{ $task->id }})">Edit</button>
                                @can('delete', $task)
                                    <button type="button" class="text-xs text-rose-600" draggable="false" @mousedown.prevent.stop wire:click.stop="deleteTask({{ $task->id }})">Delete</button>
                                @endcan
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 rounded-t-lg">
                    <h3 class="text-lg font-semibold">{{ $taskId ? 'Edit Task' : 'New Task' }}</h3>
                </div>
                
                <!-- Modal Content -->
                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="label">Title</label>
                        <input class="input w-full" wire:model.defer="form.title" />
                        @error('form.title')<div class="text-xs text-rose-600 mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-span-2">
                        <label class="label">Description</label>
                        <textarea class="input w-full" rows="2" wire:model.defer="form.description"></textarea>
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

                @if($taskId)
                    <!-- File Attachments Section (only for existing tasks) -->
                    <div class="mt-6 border-t pt-6 bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                            File Attachments
                        </h4>
                        @php
                            $task = App\Models\Task::find($taskId);
                        @endphp
                        @if($task)
                            <livewire:file-upload :model="$task" :key="'task-files-'.$taskId" />
                        @else
                            <p class="text-sm text-gray-500">Task not found.</p>
                        @endif
                    </div>
                @else
                    <!-- Info for new tasks -->
                    <div class="mt-6 border-t pt-6 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            File Attachments
                        </h4>
                        <p class="text-sm text-yellow-700 dark:text-yellow-400">
                            <strong>Save the task first</strong> to enable file attachments.
                        </p>
                    </div>
                @endif
                </div>

                <!-- Modal Footer -->
                <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-6 py-4 rounded-b-lg">
                    <div class="flex justify-end gap-2">
                        <x-secondary-button wire:click="closeTaskModal" wire:loading.attr="disabled">Cancel</x-secondary-button>
                        <x-button wire:click="saveFromForm" wire:loading.attr="disabled">
                            <span wire:loading.remove>Save</span>
                            <span wire:loading>Saving...</span>
                        </x-button>
                    </div>
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
