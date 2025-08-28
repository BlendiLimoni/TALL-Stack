<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\TaskComment;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class TaskDetails extends Component
{
    public Task $task;
    public string $newComment = '';
    public string $newSubtask = '';
    public bool $isTimeTracking = false;
    public ?TimeEntry $activeTimeEntry = null;

    public function mount(Task $task): void
    {
        $this->task = $task->load(['subtasks', 'comments.user', 'timeEntries.user', 'assignee', 'project']);
        
        // Check if user has an active time entry for this task
        $this->activeTimeEntry = TimeEntry::where('task_id', $this->task->id)
            ->where('user_id', Auth::id())
            ->whereNull('ended_at')
            ->first();
            
        $this->isTimeTracking = (bool) $this->activeTimeEntry;
    }

    public function addComment(): void
    {
        $this->validate([
            'newComment' => 'required|string|min:1',
        ]);

        TaskComment::create([
            'task_id' => $this->task->id,
            'user_id' => Auth::id(),
            'content' => $this->newComment,
        ]);

        $this->newComment = '';
        $this->task->load('comments.user');
        $this->dispatch('toast', type: 'success', message: 'Comment added');
    }

    public function addSubtask(): void
    {
        $this->validate([
            'newSubtask' => 'required|string|min:1',
        ]);

        $lastOrder = Subtask::where('task_id', $this->task->id)->max('order') ?? 0;

        Subtask::create([
            'task_id' => $this->task->id,
            'title' => $this->newSubtask,
            'order' => $lastOrder + 1,
            'created_by' => Auth::id(),
        ]);

        $this->newSubtask = '';
        $this->task->load('subtasks');
        $this->dispatch('toast', type: 'success', message: 'Subtask added');
    }

    public function toggleSubtask(int $subtaskId): void
    {
        $subtask = Subtask::findOrFail($subtaskId);
        $subtask->update(['is_completed' => !$subtask->is_completed]);
        
        $this->task->load('subtasks');
        $this->updateTaskCompletion();
    }

    public function deleteSubtask(int $subtaskId): void
    {
        Subtask::findOrFail($subtaskId)->delete();
        $this->task->load('subtasks');
        $this->updateTaskCompletion();
        $this->dispatch('toast', type: 'success', message: 'Subtask deleted');
    }

    public function startTimeTracking(): void
    {
        // End any existing active time entries for this user
        TimeEntry::where('user_id', Auth::id())
            ->whereNull('ended_at')
            ->update(['ended_at' => now()]);

        $this->activeTimeEntry = TimeEntry::create([
            'task_id' => $this->task->id,
            'user_id' => Auth::id(),
            'started_at' => now(),
        ]);

        $this->isTimeTracking = true;
        $this->dispatch('toast', type: 'success', message: 'Time tracking started');
    }

    public function stopTimeTracking(): void
    {
        if ($this->activeTimeEntry) {
            $this->activeTimeEntry->update(['ended_at' => now()]);
            $this->activeTimeEntry->save(); // Trigger duration calculation
            $this->activeTimeEntry = null;
        }

        $this->isTimeTracking = false;
        $this->task->load('timeEntries.user');
        $this->dispatch('toast', type: 'success', message: 'Time tracking stopped');
    }

    private function updateTaskCompletion(): void
    {
        $total = $this->task->subtasks()->count();
        $completed = $this->task->subtasks()->where('is_completed', true)->count();
        
        $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
        
        $this->task->update(['completion_percentage' => $percentage]);
    }

    #[On('task-updated')]
    public function refreshTask(): void
    {
        $this->task->refresh();
        $this->task->load(['subtasks', 'comments.user', 'timeEntries.user', 'assignee', 'project']);
    }

    public function render()
    {
        return view('projects.task-details');
    }
}
