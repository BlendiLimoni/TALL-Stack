<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LiveActivity extends Component
{
    public $lastActivityId = 0;
    public $newActivities = [];
    public $showNotificationDot = false;

    protected $listeners = ['activityUpdated' => 'checkForNewActivity'];

    public function mount()
    {
        $this->lastActivityId = $this->getLatestActivityId();
    }

    public function checkForNewActivity()
    {
        $this->dispatch('$refresh');
    }

    public function markAsRead()
    {
        $this->lastActivityId = $this->getLatestActivityId();
        $this->newActivities = [];
        $this->showNotificationDot = false;
    }

    private function getLatestActivityId()
    {
        if (!Auth::user()->currentTeam) {
            return 0;
        }

        return ActivityLog::where('team_id', Auth::user()->currentTeam->id)
            ->max('id') ?? 0;
    }

    public function render()
    {
        if (!Auth::user()->currentTeam) {
            return view('livewire.live-activity', [
                'recentActivities' => collect([]),
                'hasNewActivities' => false,
            ]);
        }

        $teamId = Auth::user()->currentTeam->id;
        
        // Get recent activities
        $recentActivities = ActivityLog::where('team_id', $teamId)
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        // Check for new activities since last check
        $newActivities = ActivityLog::where('team_id', $teamId)
            ->where('id', '>', $this->lastActivityId)
            ->where('user_id', '!=', Auth::id()) // Exclude current user's activities
            ->with('user')
            ->latest()
            ->get();

        $hasNewActivities = $newActivities->count() > 0;
        
        if ($hasNewActivities) {
            $this->newActivities = $newActivities->toArray();
            $this->showNotificationDot = true;
        }

        return view('livewire.live-activity', [
            'recentActivities' => $recentActivities,
            'hasNewActivities' => $hasNewActivities,
        ]);
    }

    public function formatActivityAction($action)
    {
        $actions = [
            'task.created' => 'created a task',
            'task.updated' => 'updated a task', 
            'task.deleted' => 'deleted a task',
            'task.assigned' => 'assigned a task',
            'task.moved' => 'moved a task',
            'project.created' => 'created a project',
            'project.updated' => 'updated a project',
            'project.deleted' => 'deleted a project',
            'team.joined' => 'joined the team',
            'file.uploaded' => 'uploaded a file',
        ];
        
        return $actions[$action] ?? str_replace(['.', '_'], ' ', $action);
    }

    /**
     * Helper method to create activity log entries
     */
    public static function logActivity($action, $user = null, $meta = [])
    {
        $user = $user ?? Auth::user();
        $teamId = $user?->currentTeam?->id;
        
        if (!$teamId) {
            return;
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'team_id' => $teamId,
            'action' => $action,
            'meta' => $meta,
        ]);
    }
}
