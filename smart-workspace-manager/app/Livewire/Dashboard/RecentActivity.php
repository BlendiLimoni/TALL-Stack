<?php

namespace App\Livewire\Dashboard;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RecentActivity extends Component
{
    public $activities = [];

    public function mount()
    {
        $currentTeam = Auth::user()->currentTeam;
        if ($currentTeam) {
            $projectIds = $currentTeam->projects()->pluck('id');
            $this->activities = ActivityLog::whereIn('project_id', $projectIds)
                ->latest()
                ->take(10)
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.dashboard.recent-activity');
    }
}