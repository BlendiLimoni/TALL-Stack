<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProjectStats extends Component
{
    public $stats = [];

    public function mount()
    {
        $currentTeam = Auth::user()->currentTeam;
        if ($currentTeam) {
            $projects = $currentTeam->projects()->withCount(['tasks', 'tasks as completed_tasks_count' => function ($query) {
                $query->where('status', 'done');
            }])->get();

            $this->stats = [
                'project_count' => $projects->count(),
                'task_count' => $projects->sum('tasks_count'),
                'completed_task_count' => $projects->sum('completed_tasks_count'),
            ];
        }
    }

    public function render()
    {
        return view('livewire.dashboard.project-stats');
    }
}