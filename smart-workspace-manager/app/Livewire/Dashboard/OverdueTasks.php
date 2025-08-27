<?php

namespace App\Livewire\Dashboard;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OverdueTasks extends Component
{
    public $overdueTasks = [];

    public function mount()
    {
        $this->overdueTasks = Task::where('assigned_user_id', Auth::id())
            ->where('due_date', '<', now())
            ->where('status', '!=', 'done')
            ->orderBy('due_date', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.overdue-tasks');
    }
}