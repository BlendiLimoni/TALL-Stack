<?php

namespace App\Livewire\Dashboard;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Analytics extends Component
{
    public function render()
    {
        $user = Auth::user();
        $currentTeam = $user->currentTeam;
        
        if (!$currentTeam) {
            return view('dashboard.analytics', [
                'stats' => [],
                'recentTasks' => collect([]),
                'overdueTasks' => collect([]),
            ]);
        }

        // Get team projects
        $projectIds = $currentTeam->projects()->pluck('id');
        
        // Calculate statistics
        $stats = [
            'total_tasks' => Task::whereIn('project_id', $projectIds)->count(),
            'completed_tasks' => Task::whereIn('project_id', $projectIds)->where('status', 'done')->count(),
            'in_progress_tasks' => Task::whereIn('project_id', $projectIds)->where('status', 'in_progress')->count(),
            'overdue_tasks' => Task::whereIn('project_id', $projectIds)
                ->where('due_date', '<', now())
                ->where('status', '!=', 'done')
                ->count(),
            'assigned_to_me' => Task::whereIn('project_id', $projectIds)
                ->where('assigned_user_id', $user->id)
                ->where('status', '!=', 'done')
                ->count(),
        ];

        // Get recent tasks assigned to current user
        $recentTasks = Task::whereIn('project_id', $projectIds)
            ->where('assigned_user_id', $user->id)
            ->with(['project', 'assignee'])
            ->latest()
            ->limit(5)
            ->get();

        // Get overdue tasks
        $overdueTasks = Task::whereIn('project_id', $projectIds)
            ->where('due_date', '<', now())
            ->where('status', '!=', 'done')
            ->with(['project', 'assignee'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return view('dashboard.analytics', compact('stats', 'recentTasks', 'overdueTasks'));
    }
}
