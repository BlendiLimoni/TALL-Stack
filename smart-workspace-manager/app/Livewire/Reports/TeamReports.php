<?php

namespace App\Livewire\Reports;

use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class TeamReports extends Component
{
    public string $dateRange = '7'; // days
    public ?int $selectedProject = null;
    
    public function render()
    {
        $user = Auth::user();
        $currentTeam = $user->currentTeam;
        
        if (!$currentTeam) {
            return view('reports.team-reports', [
                'projects' => collect([]),
                'analytics' => [],
                'timeReports' => [],
                'productivityMetrics' => [],
            ]);
        }

        // Get team projects for filter
        $projects = $currentTeam->projects()->get();
        
        // Date filter
        $startDate = Carbon::now()->subDays((int) $this->dateRange);
        
        // Query base - all team projects or selected project
        $projectQuery = $this->selectedProject 
            ? $currentTeam->projects()->where('id', $this->selectedProject)
            : $currentTeam->projects();
            
        $projectIds = $projectQuery->pluck('id');

        // Task analytics
        $analytics = [
            'total_tasks' => Task::whereIn('project_id', $projectIds)
                ->where('created_at', '>=', $startDate)
                ->count(),
            'completed_tasks' => Task::whereIn('project_id', $projectIds)
                ->where('status', 'done')
                ->where('updated_at', '>=', $startDate)
                ->count(),
            'overdue_tasks' => Task::whereIn('project_id', $projectIds)
                ->where('due_date', '<', now())
                ->where('status', '!=', 'done')
                ->count(),
            'avg_completion_time' => $this->getAverageCompletionTime($projectIds, $startDate),
        ];

        // Time tracking reports
        $timeReports = TimeEntry::whereHas('task', function($query) use ($projectIds) {
                $query->whereIn('project_id', $projectIds);
            })
            ->with(['user', 'task.project'])
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('ended_at')
            ->get()
            ->groupBy('user.name')
            ->map(function($entries) {
                return [
                    'total_time' => $entries->sum('duration_minutes'),
                    'task_count' => $entries->count(),
                    'avg_session' => $entries->avg('duration_minutes'),
                ];
            });

        // Productivity metrics
        $productivityMetrics = $this->getProductivityMetrics($projectIds, $startDate);

        return view('reports.team-reports', compact(
            'projects', 
            'analytics', 
            'timeReports', 
            'productivityMetrics'
        ));
    }

    private function getAverageCompletionTime(array $projectIds, Carbon $startDate): float
    {
        $completedTasks = Task::whereIn('project_id', $projectIds)
            ->where('status', 'done')
            ->where('updated_at', '>=', $startDate)
            ->get();

        if ($completedTasks->isEmpty()) {
            return 0;
        }

        $totalDays = $completedTasks->sum(function($task) {
            return $task->created_at->diffInDays($task->updated_at);
        });

        return round($totalDays / $completedTasks->count(), 1);
    }

    private function getProductivityMetrics(array $projectIds, Carbon $startDate): array
    {
        // Tasks by priority completed
        $tasksByPriority = Task::whereIn('project_id', $projectIds)
            ->where('status', 'done')
            ->where('updated_at', '>=', $startDate)
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // Daily completion trend
        $dailyCompletions = Task::whereIn('project_id', $projectIds)
            ->where('status', 'done')
            ->where('updated_at', '>=', $startDate)
            ->selectRaw('DATE(updated_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return [
            'tasks_by_priority' => $tasksByPriority,
            'daily_completions' => $dailyCompletions,
        ];
    }

    public function updatedDateRange(): void
    {
        // Component will re-render automatically
    }

    public function updatedSelectedProject(): void
    {
        // Component will re-render automatically
    }
}
