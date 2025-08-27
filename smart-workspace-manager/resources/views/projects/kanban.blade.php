<div class="p-6 space-y-6" x-data="kanban()" x-init="init()">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('projects.index') }}" class="text-sm text-gray-500 hover:underline">← Back</a>
            <h1 class="text-2xl font-semibold mt-1">{{ $project->name }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <input type="text" placeholder="Search tasks" class="input" x-model.debounce.300ms="filter" @input="$wire.set('filter', filter)" />
            <select class="input" x-model="priority" @change="$wire.set('priority', priority)">
                <option value="">All priorities</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
            <button class="px-3 py-1 rounded bg-gray-200 dark:bg-gray-700" @click="document.documentElement.classList.toggle('dark')">Dark</button>
            <x-button @click="openTaskModal()">+ Task</x-button>
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
                <div class="space-y-2" style="min-height:200px" x-data="column('{{ $key }}')" x-init="init()" @task-dropped.window="onDrop($event)">
                    @foreach(($tasks[$key] ?? collect()) as $task)
                        <div class="task-card p-3 rounded border bg-white dark:bg-gray-900 dark:border-gray-700 cursor-move" draggable="true" data-id="{{ $task->id }}" @dragstart="drag($event)" @dragover.prevent @drop="drop($event)">
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
                                <button class="text-xs text-indigo-600" @click="openTaskModal(parseInt($event.currentTarget.closest('.task-card').dataset.id), null)">Edit</button>
                                <button class="text-xs text-rose-600" @click="confirmDelete({{ $task->id }})">Delete</button>
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

    <template x-teleport="body">
        <div x-show="taskModal" x-transition class="fixed inset-0 z-50 grid place-items-center bg-black/40">
            <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-xl p-6" @click.outside="taskModal=false">
                <h3 class="text-lg font-semibold mb-4" x-text="taskId? 'Edit Task' : 'New Task'"></h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="label">Title</label>
                        <input class="input w-full" x-model="form.title" />
                    </div>
                    <div class="col-span-2">
                        <label class="label">Description</label>
                        <textarea class="input w-full" rows="4" x-model="form.description"></textarea>
                    </div>
                    <div>
                        <label class="label">Due Date</label>
                        <input type="date" class="input w-full" x-model="form.due_date" />
                    </div>
                    <div>
                        <label class="label">Priority</label>
                        <select class="input w-full" x-model="form.priority">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select class="input w-full" x-model="form.status">
                            <option value="todo">To Do</option>
                            <option value="in_progress">In Progress</option>
                            <option value="done">Done</option>
                        </select>
                    </div>
                    <div class="col-span-2" x-data="assigneePicker(@js($teamUsers))">
                        <label class="label">Assignee</label>
                        <div class="relative">
                            <input class="input w-full pr-8" placeholder="Search user..." x-model="query" @focus="open=true" @input="filter()">
                            <button type="button" class="absolute right-2 top-2 text-sm text-gray-500" @click="clear()">Clear</button>
                            <div x-show="open" @click.outside="open=false" class="absolute z-10 mt-1 w-full rounded bg-white dark:bg-gray-800 border dark:border-gray-700 max-h-56 overflow-auto">
                                <template x-for="u in results" :key="u.id">
                                    <div class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" @click="select(u)">
                                        <div class="font-medium" x-text="u.name"></div>
                                        <div class="text-xs text-gray-500" x-text="u.email"></div>
                                    </div>
                                </template>
                                <div class="px-3 py-2 text-sm text-gray-500" x-show="results.length===0">No matches</div>
                            </div>
                        </div>
                        <input type="hidden" x-model.number="form.assigned_user_id">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <x-secondary-button @click="taskModal=false">Cancel</x-secondary-button>
                    <x-button @click="save()">Save</x-button>
                </div>
            </div>
        </div>
    </template>

    @verbatim
    <script>
        function kanban() {
            return {
                filter: '',
                priority: '',
                taskModal: false,
                taskId: null,
                form: { title: '', description: '', due_date: '', assigned_user_id: '', priority: 'medium', status: 'todo' },
                init() {
                    document.addEventListener('livewire:init', () => {
                        Livewire.on('close-task-modal', () => { this.taskModal = false })
                    })
                },
                openTaskModal(id = null, json = null) {
                    this.taskId = id
                    if (json) {
                        const data = JSON.parse(json)
                        this.form = { ...this.form, ...data }
                    } else if (!id) {
                        this.form = { title: '', description: '', due_date: '', assigned_user_id: '', priority: 'medium', status: 'todo' }
                    }
                    this.taskModal = true
                },
                confirmDelete(id) {
                    if (confirm('Delete this task?')) {
                        $wire.deleteTask(id)
                    }
                },
                save() {
                    $wire.saveTask(this.taskId, this.form)
                }
            }
        }
        function column(status) {
            return {
                draggedId: null,
                init() {},
                drag(e) {
                    this.draggedId = e.target.getAttribute('data-id')
                    e.dataTransfer.setData('text/plain', JSON.stringify({ id: this.draggedId, status }))
                },
                drop(e) {
                    const data = JSON.parse(e.dataTransfer.getData('text/plain'))
                    const target = e.currentTarget
                    const children = [...target.querySelectorAll('[data-id]')]
                    const order = children.findIndex(el => el.getAttribute('data-id') === this.draggedId)
                    $wire.reorderTask(Number(data.id), status, Math.max(0, order))
                },
                onDrop(e) {}
            }
        }
        function assigneePicker(users) {
            return {
                open: false,
                query: '',
                users: users,
                results: users,
                filter() {
                    const q = this.query.toLowerCase();
                    this.results = this.users.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q))
                },
                select(u) {
                    this.query = `${u.name} <${u.email}>`
                    this.open = false
                    const root = this
                    // set parent form
                    const comp = document.querySelector('[x-data^="kanban()"]')
                    if (comp && comp.__x) {
                        // Update Alpine state
                        comp.__x.$data.form.assigned_user_id = u.id
                    }
                },
                clear(){ this.query=''; this.results=this.users; const comp=document.querySelector('[x-data^="kanban()"]'); if(comp&&comp.__x){ comp.__x.$data.form.assigned_user_id='' } }
            }
        }
    </script>
    @endverbatim
</div>
