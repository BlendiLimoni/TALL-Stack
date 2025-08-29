<?php

namespace App\Livewire\Projects;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Notifications\TaskAssigned;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Kanban extends Component
{
    use AuthorizesRequests;
    public Project $project;
    public string $filter = '';
    public string $priority = '';
    public bool $showTaskModal = false;
    public ?int $taskId = null;
    public array $form = [
        'title' => '',
        'description' => '',
        'due_date' => null,
        'assigned_user_id' => null,
        'priority' => 'medium',
        'status' => 'todo',
    ];

    public function mount(\App\Models\Project $project): void
    {
    $this->project = $project;
    // authz: ensure user is in team
    abort_unless($this->project->team->hasUser(Auth::user()), 403);
    }

    public function getTasksProperty()
    {
        return $this->project->tasks()
            ->with('assignee')
            ->when($this->filter, fn($q) => $q->where('title', 'like', "%{$this->filter}%"))
            ->when($this->priority, fn($q) => $q->where('priority', $this->priority))
            ->get()
            ->groupBy('status');
    }

    public function reorderTask(array $payload): void
    {
        $taskId = $payload['taskId'];
        $status = $payload['status'];
        $order = $payload['order'];

        $task = Task::findOrFail($taskId);
        $this->authorize('update', $task);

        // Shift orders within the target status
        Task::where('project_id', $task->project_id)
            ->where('status', $status)
            ->where('id', '!=', $task->id)
            ->where('order', '>=', $order)
            ->increment('order');

        // Avoid Scout indexing on simple reorder
        Task::withoutSyncingToSearch(function () use ($task, $status, $order) {
            $task->update([
                'status' => $status,
                'order' => $order,
            ]);
        });

        ActivityLog::create([
            'team_id' => $task->project->team_id,
            'user_id' => Auth::id(),
            'action' => 'task.moved',
            'subject_type' => Task::class,
            'subject_id' => $task->id,
                            'meta' => ['status' => $status, 'order' => $order]

        ]);

        $this->dispatch('toast', type: 'success', message: 'Task updated');
        $this->dispatch('$refresh');
    }

    public function getTaskData(int $taskId): array
    {
        $task = Task::findOrFail($taskId);
        $this->authorize('view', $task);
        
        return [
            'title' => $task->title,
            'description' => $task->description,
            'due_date' => $task->due_date?->format('Y-m-d'),
            'assigned_user_id' => $task->assigned_user_id,
            'priority' => $task->priority,
            'status' => $task->status,
        ];
    }

    public function openTaskModal(?int $taskId = null): void
    {
        $this->taskId = $taskId;
        if ($taskId) {
            $data = $this->getTaskData($taskId);
            $this->form = array_merge($this->form, $data);
        } else {
            $this->form = [
                'title' => '',
                'description' => '',
                'due_date' => null,
                'assigned_user_id' => null,
                'priority' => 'medium',
                'status' => 'todo',
            ];
        }
        $this->showTaskModal = true;
    }

    public function closeTaskModal(): void
    {
        $this->showTaskModal = false;
    }

    public function saveTask(array $payload): void
    {
        $taskId = $payload['taskId'] ?? null;
        $data = $payload['data'];
        // Coerce optional blanks to null
        foreach (['assigned_user_id','due_date'] as $opt) {
            if (!isset($data[$opt]) || $data[$opt] === '' || $data[$opt] === false) {
                $data[$opt] = null;
            }
        }

        $validated = validator($data, [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'status' => ['required', 'in:todo,in_progress,done'],
        ])->validate();

        if ($taskId) {
            $task = Task::findOrFail($taskId);
            $this->authorize('update', $task);
            $originalAssignee = $task->assigned_user_id;
            Task::withoutSyncingToSearch(function () use ($task, $validated) {
                $task->update($validated);
            });
            if (!empty($validated['assigned_user_id']) && $validated['assigned_user_id'] !== $originalAssignee) {
                optional(\App\Models\User::find($validated['assigned_user_id']))?->notify(new TaskAssigned($task));
            }
            ActivityLog::create([
                'team_id' => $task->project->team_id,
                'user_id' => Auth::id(),
                'action' => 'task.updated',
                'subject_type' => Task::class,
                'subject_id' => $task->id,
                'meta' => ['title' => $task->title],
            ]);
        } else {
            $this->authorize('create', [Task::class, $this->project]);
            $lastOrder = Task::where('project_id', $this->project->id)
                ->where('status', $validated['status'])
                ->max('order') ?? 0;
            $validated['order'] = $lastOrder + 1;
            $validated['project_id'] = $this->project->id;
            $validated['created_by'] = Auth::id();
            Task::withoutSyncingToSearch(function () use (&$task, $validated) {
                $task = Task::create($validated);
            });
            if (!empty($validated['assigned_user_id'])) {
                optional(\App\Models\User::find($validated['assigned_user_id']))?->notify(new TaskAssigned($task));
            }
            ActivityLog::create([
                'team_id' => $this->project->team_id,
                'user_id' => Auth::id(),
                'action' => 'task.created',
                'subject_type' => Task::class,
                'subject_id' => $task->id,
                'meta' => ['title' => $task->title],
            ]);
        }

        $this->dispatch('close-task-modal');
        $this->dispatch('$refresh');
    }

    public function saveFromForm(): void
    {
        $this->saveTask(['taskId' => $this->taskId, 'data' => $this->form]);
        $this->showTaskModal = false;
    }

    public function deleteTask(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        $this->authorize('delete', $task);
        // Remove from search before deleting to avoid indexing issues
        try { $task->unsearchable(); } catch (\Throwable $e) {}
        Task::withoutSyncingToSearch(function () use ($task) {
            $task->delete();
        });
        ActivityLog::create([
            'team_id' => $this->project->team_id,
            'user_id' => Auth::id(),
            'action' => 'task.deleted',
            'subject_type' => Task::class,
            'subject_id' => $taskId,
        ]);
        $this->dispatch('toast', type: 'success', message: 'Task deleted');
        $this->dispatch('$refresh');
    }

    public function render()
    {
        $logs = \App\Models\ActivityLog::with('user')
            ->where('team_id', $this->project->team_id)
            ->latest()
            ->limit(10)
            ->get();

        // Get all team users including the owner
        $teamUsers = $this->project->team->users()->select('users.id','users.name','users.email')->get();
        
        // Add the team owner if not already included
        $owner = $this->project->team->owner;
        if (!$teamUsers->contains('id', $owner->id)) {
            $teamUsers->push((object)[
                'id' => $owner->id,
                'name' => $owner->name,
                'email' => $owner->email
            ]);
        }
        
        // Sort by name for better UX
        $teamUsers = $teamUsers->sortBy('name')->values();

    return view('projects.kanban_page', compact('logs','teamUsers'));
    }
}
