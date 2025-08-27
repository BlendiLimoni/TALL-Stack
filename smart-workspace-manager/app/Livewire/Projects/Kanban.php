<?php

namespace App\Livewire\Projects;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Notifications\TaskAssigned;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class Kanban extends Component
{
    use AuthorizesRequests;
    public Project $project;
    public string $filter = '';
    public string $priority = '';

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

    #[On('reorder-task')]
    public function reorderTask(int $taskId, string $status, int $order): void
    {
    $task = Task::findOrFail($taskId);
    $this->authorize('update', $task);

        // Shift orders within the target status
        Task::where('project_id', $task->project_id)
            ->where('status', $status)
            ->where('id', '!=', $task->id)
            ->where('order', '>=', $order)
            ->increment('order');

        $task->update([
            'status' => $status,
            'order' => $order,
        ]);

        ActivityLog::create([
            'team_id' => $task->project->team_id,
            'user_id' => Auth::id(),
            'action' => 'task.moved',
            'subject_type' => Task::class,
            'subject_id' => $task->id,
            'meta' => ['status' => $status, 'order' => $order],
        ]);

        $this->dispatch('toast', type: 'success', message: 'Task updated');
        $this->dispatch('$refresh');
    }

    #[On('save-task')]
    public function saveTask(?int $taskId, array $data): void
    {
        $validated = validator($data, [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'status' => ['required', 'in:todo,in_progress,done'],
        ])->validate();

        if ($taskId) {
            $task = Task::findOrFail($taskId);
            $this->authorize('update', $task);
            $originalAssignee = $task->assigned_user_id;
            $task->update($validated);
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
            $task = Task::create($validated);
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

    public function deleteTask(int $taskId): void
    {
    $task = Task::findOrFail($taskId);
    $this->authorize('delete', $task);
        $task->delete();
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

        $teamUsers = $this->project->team->users()->select('users.id','users.name','users.email')->get();

        return view('projects.kanban', compact('logs','teamUsers'));
    }
}
